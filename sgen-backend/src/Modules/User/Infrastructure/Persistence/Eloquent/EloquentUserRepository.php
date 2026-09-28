<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Modules\Support\Domain\Enums\TicketStatus;
use Modules\User\Domain\Enums\UserRole;
use Modules\User\Domain\Exceptions\UserDeletionFailedException;
use Modules\User\Domain\Models\SystemUser;
use Modules\User\Domain\Ports\UserRepositoryInterface;

final class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<array<string, mixed>>
     */
    public function listWithDetails(array $filters = []): array
    {
        $query = DB::table('usuarios')
            ->leftJoin('departamentos', 'usuarios.departamento_id', '=', 'departamentos.id')
            ->leftJoin('empleados', 'usuarios.empleado_id', '=', 'empleados.id')
            ->select(
                'usuarios.id',
                'usuarios.username',
                'usuarios.rol',
                'usuarios.tema',
                'usuarios.departamento_id',
                'usuarios.activo',
                'departamentos.nombre as departamento_nombre',
                'usuarios.empleado_id',
                'empleados.nombre as empleado_nombre',
                'empleados.apellido as empleado_apellido',
                'empleados.email as empleado_email'
            );

        if (! empty($filters['search'])) {
            $search = '%'.trim((string) $filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->where('usuarios.username', 'like', $search)
                    ->orWhere('empleados.nombre', 'like', $search)
                    ->orWhere('empleados.apellido', 'like', $search)
                    ->orWhere('departamentos.nombre', 'like', $search);
            });
        }

        if (! empty($filters['rol'])) {
            $query->where('usuarios.rol', (string) $filters['rol']);
        }

        return $query->orderBy('usuarios.id')->get()->all();
    }

    public function findById(int $id): ?SystemUser
    {
        $row = DB::table('usuarios')->where('id', $id)->first();
        if ($row === null) {
            return null;
        }

        return SystemUser::create(
            username: (string) $row->username,
            password: (string) $row->password,
            role: UserRole::tryFromString((string) $row->rol),
            theme: (string) ($row->tema ?? 'light'),
            employeeId: $row->empleado_id !== null ? (int) $row->empleado_id : null,
            departmentId: $row->departamento_id !== null ? (int) $row->departamento_id : null,
            id: (int) $row->id,
            email: $row->email ?? null,
            active: (bool) ($row->activo ?? true)
        );
    }

    public function findByUsername(string $username): ?SystemUser
    {
        $row = DB::table('usuarios')->where('username', $username)->first();
        if ($row === null) {
            return null;
        }

        return SystemUser::create(
            username: (string) $row->username,
            password: (string) $row->password,
            role: UserRole::tryFromString((string) $row->rol),
            theme: (string) ($row->tema ?? 'light'),
            employeeId: $row->empleado_id !== null ? (int) $row->empleado_id : null,
            departmentId: $row->departamento_id !== null ? (int) $row->departamento_id : null,
            id: (int) $row->id,
            email: $row->email ?? null,
            active: (bool) ($row->activo ?? true)
        );
    }

    public function countAdmins(): int
    {
        return (int) DB::table('usuarios')->where('rol', 'admin')->count();
    }

    public function countActiveAdmins(): int
    {
        return (int) DB::table('usuarios')
            ->where('rol', 'admin')
            ->where('activo', true)
            ->count();
    }

    public function activeTicketIdsAssignedTo(int $userId): array
    {
        $empleadoId = DB::table('usuarios')->where('id', $userId)->value('empleado_id');

        if ($empleadoId === null) {
            return [];
        }

        return DB::table('soportes')
            ->where('empleado_id', (int) $empleadoId)
            ->whereNotIn('estado', TicketStatus::finalValues())
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    public function save(SystemUser $user): int
    {
        $id = (int) DB::table('usuarios')->insertGetId([
            'username' => $user->username(),
            'password' => $user->password(),
            'rol' => $user->role()->value,
            'tema' => $user->theme(),
            'empleado_id' => $user->employeeId(),
            'departamento_id' => $user->departmentId(),
            'email' => $user->email(),
            // Cuenta nueva creada por administración: cambio obligatorio en el primer ingreso.
            'must_change_password' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // La escritura va por Query Builder (no dispara eventos Eloquent):
        // el rol Spatie se sincroniza aquí para que los permisos coincidan
        // con la columna `rol` desde el primer login.
        $this->syncSpatieRole($id);

        return $id;
    }

    public function findByEmpleadoId(int $empleadoId): ?SystemUser
    {
        $row = DB::table('usuarios')->where('empleado_id', $empleadoId)->first();
        if ($row === null) {
            // Puente legacy: el empleado puede referenciar al usuario desde su lado.
            $row = DB::table('usuarios')
                ->whereIn('id', fn ($q) => $q->select('usuario_id')->from('empleados')->where('id', $empleadoId))
                ->first();
        }
        if ($row === null) {
            return null;
        }

        return SystemUser::create(
            username: (string) $row->username,
            password: (string) $row->password,
            role: UserRole::tryFromString((string) $row->rol),
            theme: (string) ($row->tema ?? 'light'),
            employeeId: $row->empleado_id !== null ? (int) $row->empleado_id : null,
            departmentId: $row->departamento_id !== null ? (int) $row->departamento_id : null,
            id: (int) $row->id,
            email: $row->email ?? null,
            active: (bool) ($row->activo ?? true)
        );
    }

    public function update(SystemUser $user): void
    {
        DB::table('usuarios')
            ->where('id', $user->id())
            ->update([
                'username' => $user->username(),
                'rol' => $user->role()->value,
                'empleado_id' => $user->employeeId(),
                'departamento_id' => $user->departmentId(),
                'email' => $user->email(),
                'updated_at' => now(),
            ]);

        // Sin esto, degradar un admin solo cambiaba la columna: el rol Spatie
        // seguía otorgando permisos de administrador.
        $this->syncSpatieRole($user->id());
    }

    public function updatePassword(int $id, string $hashedPassword, bool $mustChange = false): void
    {
        DB::table('usuarios')
            ->where('id', $id)
            ->update([
                'password' => $hashedPassword,
                'must_change_password' => $mustChange,
                'updated_at' => now(),
            ]);
    }

    public function operationalReferenceCounts(int $userId): array
    {
        $conteos = [
            'movimientos de inventario' => (int) DB::table('inventario_movimientos')->where('usuario_id', $userId)->count(),
            'comentarios en tickets' => (int) DB::table('ticket_comentarios')->where('usuario_id', $userId)->count(),
        ];

        return array_filter($conteos, static fn (int $filas): bool => $filas > 0);
    }

    public function setActive(int $id, bool $active): void
    {
        DB::transaction(function () use ($id, $active): void {
            $changes = ['activo' => $active, 'updated_at' => now()];

            if (! $active) {
                // Sin "recordarme": la cookie de recuerdo no debe resucitar
                // una cuenta desactivada en la siguiente petición.
                $changes['remember_token'] = null;
            }

            DB::table('usuarios')->where('id', $id)->update($changes);

            if (! $active) {
                // Revocación inmediata de sesiones vivas de la cuenta desactivada.
                DB::table('sessions')->where('user_id', $id)->delete();
                $this->closeOpenSessionLogs($id, 'desactivacion');
            }
        });
    }

    /**
     * Cierra los registros forenses de sesión que quedaron abiertos para que
     * la auditoría no siga mostrando sesiones "activas" de una cuenta revocada.
     */
    private function closeOpenSessionLogs(int $userId, string $motivo): void
    {
        DB::table('sesiones_log')
            ->where('usuario_id', $userId)
            ->whereNull('fecha_fin')
            ->update(['fecha_fin' => now(), 'motivo_cierre' => $motivo]);
    }

    private function syncSpatieRole(int $id): void
    {
        User::query()->find($id)?->syncSpatieRoleFromColumn();
    }

    public function delete(int $id): void
    {
        try {
            DB::table('usuarios')->where('id', $id)->delete();
        } catch (QueryException $e) {
            // 23503: violación de llave foránea (algún residuo no cubierto por
            // las validaciones de negocio). Se traduce a error de dominio.
            if ($e->getCode() === '23503') {
                throw UserDeletionFailedException::referenciasVinculadas(
                    DB::table('usuarios')->where('id', $id)->value('username') ?? (string) $id
                );
            }

            throw $e;
        }
    }

    public function getKpis(): array
    {
        $totalUsers = DB::table('usuarios')->count();
        $admins = DB::table('usuarios')->where('rol', 'admin')->count();
        $techs = DB::table('usuarios')->where('rol', 'tecnico')->count();

        return [
            'total_users' => $totalUsers,
            'admins_count' => $admins,
            'techs_count' => $techs,
        ];
    }

    public function getDepartmentsLookup(): array
    {
        return DB::table('departamentos')
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get()
            ->map(fn ($d) => ['id' => (int) $d->id, 'nombre' => (string) $d->nombre])
            ->all();
    }

    public function getEmployeesLookup(): array
    {
        return DB::table('empleados')
            ->select('id', 'nombre', 'apellido', 'departamento_id')
            ->orderBy('nombre')
            ->get()
            ->map(fn ($e) => [
                'id' => (int) $e->id,
                'nombre' => (string) $e->nombre,
                'apellido' => (string) $e->apellido,
                'departamento_id' => $e->departamento_id !== null ? (int) $e->departamento_id : null,
            ])
            ->all();
    }
}
