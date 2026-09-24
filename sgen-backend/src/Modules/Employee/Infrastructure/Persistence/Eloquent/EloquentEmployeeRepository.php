<?php

declare(strict_types=1);

namespace Modules\Employee\Infrastructure\Persistence\Eloquent;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Employee\Application\DTOs\CreateEmployeeDTO;
use Modules\Employee\Application\DTOs\EmployeeDetailDTO;
use Modules\Employee\Application\DTOs\EmployeeKpisDTO;
use Modules\Employee\Application\DTOs\EmployeeListItemDTO;
use Modules\Employee\Application\DTOs\UpdateEmployeeDTO;
use Modules\Employee\Application\Mappers\EmployeeDetailMapper;
use Modules\Employee\Application\Mappers\EmployeeListItemMapper;
use Modules\Employee\Domain\Exceptions\EmployeeNotFoundException;
use Modules\Employee\Domain\Ports\EmployeeRepositoryInterface;

final class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function getKpis(): EmployeeKpisDTO
    {
        $totalEmp = (int) DB::table('empleados')->whereNull('deleted_at')->count();
        $activeUsers = (int) DB::table('empleados')
            ->whereNull('deleted_at')
            ->whereNotNull('usuario_id')
            ->count();
        $noUsers = max(0, $totalEmp - $activeUsers);

        return new EmployeeKpisDTO(
            totalEmp: $totalEmp,
            activeUsers: $activeUsers,
            noUsers: $noUsers
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return EmployeeListItemDTO[]
     */
    public function list(array $filters = []): array
    {
        $query = DB::table('empleados')
            ->leftJoin('departamentos', 'empleados.departamento_id', '=', 'departamentos.id')
            ->leftJoin('usuarios', 'empleados.usuario_id', '=', 'usuarios.id')
            ->select([
                'empleados.*',
                'departamentos.nombre as departamento_nombre',
                'usuarios.username as username',
            ])
            ->whereNull('empleados.deleted_at')
            ->orderBy('empleados.id', 'asc');

        // Filtro por departamento (nombre o id)
        if (!empty($filters['departamento']) && $filters['departamento'] !== 'all') {
            $query->where('departamentos.nombre', $filters['departamento']);
        }
        if (!empty($filters['departamento_id'])) {
            $query->where('empleados.departamento_id', (int) $filters['departamento_id']);
        }

        // Filtro por KPI (active / inactive)
        if (!empty($filters['kpi'])) {
            if ($filters['kpi'] === 'active') {
                $query->whereNotNull('empleados.usuario_id');
            } elseif ($filters['kpi'] === 'inactive') {
                $query->whereNull('empleados.usuario_id');
            }
        }

        // Búsqueda general
        if (!empty($filters['search'])) {
            $term = '%' . trim((string) $filters['search']) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('empleados.nombre', 'ilike', $term)
                    ->orWhere('empleados.apellido', 'ilike', $term)
                    ->orWhere('empleados.email', 'ilike', $term)
                    ->orWhere('empleados.cedula', 'ilike', $term)
                    ->orWhere('empleados.cargo', 'ilike', $term)
                    ->orWhere('departamentos.nombre', 'ilike', $term)
                    ->orWhere('usuarios.username', 'ilike', $term);
            });
        }

        return $query->get()
            ->map(fn($row) => EmployeeListItemMapper::fromRow($row))
            ->all();
    }

    public function findById(int $id): ?EmployeeDetailDTO
    {
        $row = DB::table('empleados')
            ->leftJoin('departamentos', 'empleados.departamento_id', '=', 'departamentos.id')
            ->leftJoin('usuarios', 'empleados.usuario_id', '=', 'usuarios.id')
            ->select([
                'empleados.*',
                'departamentos.nombre as departamento_nombre',
                'usuarios.username as username',
            ])
            ->where('empleados.id', $id)
            ->whereNull('empleados.deleted_at')
            ->first();

        if ($row === null) {
            return null;
        }

        return EmployeeDetailMapper::fromRow($row);
    }

    public function create(CreateEmployeeDTO $dto): int
    {
        $now = Carbon::now();

        $id = (int) DB::table('empleados')->insertGetId([
            'nombre' => $dto->nombre,
            'apellido' => $dto->apellido,
            'email' => $dto->email,
            'cedula' => $dto->cedula,
            'cargo' => $dto->cargo,
            'departamento_id' => $dto->departamentoId,
            'rol' => $dto->rol,
            'usuario_id' => $dto->usuarioId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($dto->usuarioId !== null) {
            DB::table('usuarios')
                ->where('id', $dto->usuarioId)
                ->update([
                    'empleado_id' => $id,
                    'departamento_id' => $dto->departamentoId,
                ]);
        }

        return $id;
    }

    public function update(int $id, UpdateEmployeeDTO $dto): void
    {
        $exists = DB::table('empleados')->where('id', $id)->whereNull('deleted_at')->exists();
        if (!$exists) {
            throw EmployeeNotFoundException::withId($id);
        }

        $payload = ['updated_at' => Carbon::now()];

        if ($dto->nombre !== null) {
            $payload['nombre'] = $dto->nombre;
        }
        if ($dto->apellido !== null) {
            $payload['apellido'] = $dto->apellido;
        }
        if ($dto->email !== null) {
            $payload['email'] = $dto->email;
        }
        if ($dto->cedula !== null) {
            $payload['cedula'] = $dto->cedula;
        }
        if ($dto->cargo !== null) {
            $payload['cargo'] = $dto->cargo;
        }
        if ($dto->departamentoId !== null) {
            $payload['departamento_id'] = $dto->departamentoId;
        }
        if ($dto->rol !== null) {
            $payload['rol'] = $dto->rol;
        }
        if ($dto->usuarioId !== null) {
            $payload['usuario_id'] = $dto->usuarioId;

            DB::table('usuarios')
                ->where('id', $dto->usuarioId)
                ->update(['empleado_id' => $id]);
        }

        DB::table('empleados')->where('id', $id)->update($payload);
    }

    public function delete(int $id): void
    {
        $now = Carbon::now();
        $updated = DB::table('empleados')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => $now,
                'usuario_id' => null,
            ]);

        if ($updated === 0) {
            throw EmployeeNotFoundException::withId($id);
        }

        // Desvincular en usuarios
        DB::table('usuarios')->where('empleado_id', $id)->update(['empleado_id' => null]);
    }

    public function getFormOptions(): array
    {
        $departments = DB::table('departamentos')
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->get()
            ->map(fn($d) => ['id' => (int) $d->id, 'nombre' => (string) $d->nombre])
            ->all();

        $users = DB::table('usuarios')
            ->select(['id', 'username', 'rol'])
            ->orderBy('username')
            ->get()
            ->map(fn($u) => [
                'id' => (int) $u->id,
                'username' => '@' . ltrim((string) $u->username, '@'),
                'rol' => (string) $u->rol,
            ])
            ->all();

        return [
            'departments' => $departments,
            'users' => $users,
        ];
    }
}
