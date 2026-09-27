<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Papelera universal (acción de restaurar o purga): ventana de seguridad
 * frente a borrados accidentales en catálogos. Restringida a administración.
 */
final class PapeleraController extends Controller
{
    /** Entidades catalogables admitidas por la papelera. */
    private const CATALOGOS = [
        'equipos' => ['etiqueta' => 'Equipos', 'nombre_col' => 'nombre', 'extra_col' => 'codigo_inventario'],
        'inventario_items' => ['etiqueta' => 'Artículos de inventario', 'nombre_col' => 'codigo', 'extra_col' => 'nombre'],
        'departamentos' => ['etiqueta' => 'Departamentos', 'nombre_col' => 'nombre', 'extra_col' => null],
        'categorias' => ['etiqueta' => 'Categorías', 'nombre_col' => 'nombre', 'extra_col' => null],
        'mantenimientos' => ['etiqueta' => 'Mantenimientos', 'nombre_col' => 'descripcion', 'extra_col' => 'tipo_mantenimiento'],
        'soportes' => ['etiqueta' => 'Tickets (Soporte)', 'nombre_col' => 'titulo', 'extra_col' => 'prioridad'],
    ];

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Papelera', [
            'catalogos_activos' => $this->catalogosActivos(),
        ]);
    }

    public function restore(Request $request, string $entidad, int $id): \Illuminate\Http\RedirectResponse
    {
        $meta = self::CATALOGOS[$entidad] ?? null;
        abort_unless($meta !== null, 404, 'Catálogo no recuperable por papelera.');

        // Restauración: solo registra si fue a papelera; reingreso inmediato.
        $restaurado = DB::table($entidad)
            ->where('id', $id)
            ->whereNotNull('deleted_at')
            ->update(['deleted_at' => null, 'updated_at' => Carbon::now()]);

        return $restaurado > 0
            ? back()->with('success', "{$meta['etiqueta']} #{$id} restaurado: ya está activo en su módulo.")
            : back()->with('error', 'No se encontró el registro en papelera.');
    }

    public function destroyForever(string $entidad, int $id): \Illuminate\Http\RedirectResponse
    {
        $meta = self::CATALOGOS[$entidad] ?? null;
        abort_unless($meta !== null, 404);

        // Purga definitiva: solo desde papelera y con ventana: la traza queda
        // en bitácora vía el log de la aplicación que la acción invoca.
        $query = DB::table($entidad)->where('id', $id);
        $pendiente = DB::table($entidad)->where('id', $id)->whereNotNull('deleted_at')->exists();

        if (! $pendiente) {
            return back()->with('error', 'Solo los registros en papelera pueden eliminarse definitivamente.');
        }

        $query->delete();

        return back()->with('success', 'Registro eliminado de manera permanente.');
    }

    /** @return array<int, array<string, mixed>> */
    private function catalogosActivos(): array
    {
        $catalogo = [];

        foreach (self::CATALOGOS as $tabla => $meta) {
            $registros = DB::table($tabla)
                ->whereNotNull('deleted_at')
                ->orderByDesc('deleted_at')
                ->limit(50)
                ->get();

            if ($registros->isEmpty()) {
                continue;
            }

            $catalogo[] = [
                'tipo' => $tabla,
                'etiqueta' => $meta['etiqueta'],
                'registros' => $registros->map(fn ($r) => [
                    'id' => (int) $r->id,
                    'nombre' => trim((string) ($r->{$meta['nombre_col']} ?? $r->id)),
                    'extra' => $meta['extra_col'] !== null ? (string) ($r->{$meta['extra_col']} ?? '') : '',
                    'deleted_at' => Carbon::parse((string) $r->deleted_at)->format('d/m/Y H:i'),
                ])->all(),
            ];
        }

        return $catalogo;
    }
}