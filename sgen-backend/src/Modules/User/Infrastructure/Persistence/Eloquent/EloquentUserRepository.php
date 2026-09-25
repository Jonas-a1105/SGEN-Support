<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Persistence\Eloquent;

use Illuminate\Support\Facades\DB;
use Modules\User\Domain\Enums\UserRole;
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
            email: $row->email ?? null
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
            email: $row->email ?? null
        );
    }

    public function countAdmins(): int
    {
        return (int) DB::table('usuarios')->where('rol', 'admin')->count();
    }

    public function save(SystemUser $user): int
    {
        return (int) DB::table('usuarios')->insertGetId([
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
            email: $row->email ?? null
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

    public function delete(int $id): void
    {
        DB::table('usuarios')->where('id', $id)->delete();
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
