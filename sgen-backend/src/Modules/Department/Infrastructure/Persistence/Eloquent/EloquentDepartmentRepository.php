<?php

declare(strict_types=1);

namespace Modules\Department\Infrastructure\Persistence\Eloquent;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Department\Application\DTOs\CreateDepartmentDTO;
use Modules\Department\Application\DTOs\DepartmentDetailDTO;
use Modules\Department\Application\DTOs\DepartmentListItemDTO;
use Modules\Department\Application\DTOs\UpdateDepartmentDTO;
use Modules\Department\Application\Mappers\DepartmentDetailMapper;
use Modules\Department\Application\Mappers\DepartmentListItemMapper;
use Modules\Department\Domain\Exceptions\DepartmentNotFoundException;
use Modules\Department\Domain\Ports\DepartmentRepositoryInterface;

final class EloquentDepartmentRepository implements DepartmentRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return DepartmentListItemDTO[]
     */
    public function list(array $filters = []): array
    {
        $query = DB::table('departamentos')
            ->select([
                'departamentos.*',
                DB::raw('(SELECT COUNT(*) FROM equipos WHERE equipos.departamento_id = departamentos.id) as equipos_count'),
                DB::raw('(SELECT COUNT(*) FROM empleados WHERE empleados.departamento_id = departamentos.id AND empleados.deleted_at IS NULL) as empleados_count'),
            ])
            ->orderBy('departamentos.id', 'asc');

        if (!empty($filters['search'])) {
            $term = '%' . trim((string) $filters['search']) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('departamentos.nombre', 'ilike', $term)
                    ->orWhere('departamentos.ubicacion', 'ilike', $term)
                    ->orWhere('departamentos.jefe_area_nombre', 'ilike', $term)
                    ->orWhere('departamentos.descripcion', 'ilike', $term);
            });
        }

        return $query->get()
            ->map(fn($row) => DepartmentListItemMapper::fromRow($row))
            ->all();
    }

    public function findById(int $id): ?DepartmentDetailDTO
    {
        $row = DB::table('departamentos')
            ->select([
                'departamentos.*',
                DB::raw('(SELECT COUNT(*) FROM equipos WHERE equipos.departamento_id = departamentos.id) as equipos_count'),
                DB::raw('(SELECT COUNT(*) FROM empleados WHERE empleados.departamento_id = departamentos.id AND empleados.deleted_at IS NULL) as empleados_count'),
            ])
            ->where('departamentos.id', $id)
            ->first();

        if ($row === null) {
            return null;
        }

        return DepartmentDetailMapper::fromRow($row);
    }

    public function create(CreateDepartmentDTO $dto): int
    {
        $now = Carbon::now();

        $id = (int) DB::table('departamentos')->insertGetId([
            'nombre' => $dto->nombre,
            'ubicacion' => $dto->ubicacion,
            'jefe_area_nombre' => $dto->jefeAreaNombre,
            'jefe_area_id' => $dto->jefeAreaId,
            'descripcion' => $dto->descripcion,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $id;
    }

    public function update(int $id, UpdateDepartmentDTO $dto): void
    {
        $exists = DB::table('departamentos')->where('id', $id)->exists();
        if (!$exists) {
            throw DepartmentNotFoundException::withId($id);
        }

        $payload = ['updated_at' => Carbon::now()];

        if ($dto->nombre !== null) {
            $payload['nombre'] = $dto->nombre;
        }
        if ($dto->ubicacion !== null) {
            $payload['ubicacion'] = $dto->ubicacion;
        }
        if ($dto->jefeAreaNombre !== null) {
            $payload['jefe_area_nombre'] = $dto->jefeAreaNombre;
        }
        if ($dto->jefeAreaId !== null) {
            $payload['jefe_area_id'] = $dto->jefeAreaId;
        }
        if ($dto->descripcion !== null) {
            $payload['descripcion'] = $dto->descripcion;
        }

        DB::table('departamentos')->where('id', $id)->update($payload);
    }

    public function delete(int $id): void
    {
        // Desvincular equipos y empleados
        DB::table('equipos')->where('departamento_id', $id)->update(['departamento_id' => null]);
        DB::table('empleados')->where('departamento_id', $id)->update(['departamento_id' => null]);

        $deleted = DB::table('departamentos')->where('id', $id)->delete();
        if ($deleted === 0) {
            throw DepartmentNotFoundException::withId($id);
        }
    }

    public function getCandidatesForLeadership(): array
    {
        return DB::table('empleados')
            ->select(['id', 'nombre', 'apellido', 'cargo'])
            ->whereNull('deleted_at')
            ->orderBy('nombre')
            ->get()
            ->map(function ($e) {
                $firstName = trim((string) $e->nombre);
                $lastName = trim((string) ($e->apellido ?? ''));
                $fullName = trim($firstName . ' ' . $lastName);
                $init = mb_strtoupper(mb_substr($firstName, 0, 1) . mb_substr($lastName, 0, 1));

                return [
                    'id' => (int) $e->id,
                    'name' => $fullName,
                    'role' => (string) ($e->cargo ?? 'Personal'),
                    'init' => $init !== '' ? $init : 'EM',
                ];
            })
            ->all();
    }
}
