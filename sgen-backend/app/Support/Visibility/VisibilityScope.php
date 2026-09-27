<?php

declare(strict_types=1);

namespace App\Support\Visibility;

use Illuminate\Database\Query\Builder;

/**
 * Scope de lectura por rol (regla de visibilidad del negocio):
 *
 * - admin / tecnico → global (su trabajo es transversal).
 * - consultor → global de solo lectura (analítico; las pantallas operativas
 *   se cierran por permiso, no por scope).
 * - operador → SOLO su esfera: el departamento del empleado vinculado a su
 *   cuenta. Ve sus activos, sus tickets y su gente — el resto queda fuera
 *   del alcance permanente de la petición.
 *
 * Se aplica una sola vez por consulta; se negocia nunca a nivel de
 * partial queries individuales.
 */
final class VisibilityScope
{
    /**
     * Aplica el filtro de lectura a un builder para una entidad cuya
     * columna de departamento es `{$tabla}.departamento_id` (o similar).
     * Devuelve el builder filtrado si corresponde, o el original.
     */
    public static function applyToDepartamentos(
        Builder $query,
        string $tablaDepartamentoCampo
    ): Builder {
        $user = auth()->user();

        if ($user === null || ! $user instanceof \App\Models\User) {
            return $query;
        }

        $rol = (string) $user->rol;
        if (in_array($rol, ['admin', 'tecnico', 'consultor'], true)) {
            return $query;
        }

        // Operativo: solo su área.  La cuenta operadora lleva su departamento
        // en usuarios.departamento_id (y/o empleado.departamento_id) — usamos
        // ambos para no abusar de la normalización.
        $deptosVisibles = self::departamentosDelUsuario($user);

        if ($deptosVisibles === []) {
            // Sin departamento asignado: su rol lo protege estrictamente, no
            // opcional — nada más llega a su alcance.
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($tablaDepartamentoCampo, $deptosVisibles);
    }

    /**
     * Deparamentos legibles del usuario = departamento de su cuenta (o la del
     * empleado vinculado). Jamás se blanquea.
     *
     * @return list<int>
     */
    private static function departamentosDelUsuario(\App\Models\User $user): array
    {
        $cohere = [];

        if ($user->departamento_id !== null) {
            $cohere[] = (int) $user->departamento_id;
        }

        if ($user->empleado_id !== null) {
            $depto = \Illuminate\Support\Facades\DB::table('empleados')
                ->where('id', (int) $user->empleado_id)
                ->value('departamento_id');

            if ($depto !== null) {
                $cohere[] = (int) $depto;
            }
        }

        return array_values(array_unique($cohere));
    }

}
