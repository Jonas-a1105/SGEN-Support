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

    public function getCompleteDetail(int $id): ?DepartmentDetailDTO
    {
        $row = DB::table('departamentos')->where('id', $id)->first();
        if ($row === null) {
            return null;
        }

        // Jefe info
        $jefeEmail = null;
        $jefeTelefono = null;
        $jefeCargo = null;
        $jefeInitials = '--';

        if (!empty($row->jefe_area_id)) {
            $jefe = DB::table('empleados')->where('id', $row->jefe_area_id)->first();
            if ($jefe !== null) {
                $jefeEmail = $jefe->email ?? null;
                $jefeTelefono = $jefe->telefono ?? null;
                $jefeCargo = $jefe->cargo ?? 'Jefe de Departamento';
                $jefeInitials = mb_strtoupper(mb_substr($jefe->nombre, 0, 1) . mb_substr($jefe->apellido ?? '', 0, 1));
            }
        } elseif (!empty($row->jefe_area_nombre)) {
            $parts = explode(' ', trim((string) $row->jefe_area_nombre));
            $jefeInitials = mb_strtoupper(mb_substr($parts[0], 0, 1) . (isset($parts[1]) ? mb_substr($parts[1], 0, 1) : ''));
        }

        // Empleados asignados al departamento
        $empleados = DB::table('empleados')
            ->where('departamento_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('nombre')
            ->get()
            ->map(function ($e) {
                $fn = trim($e->nombre . ' ' . ($e->apellido ?? ''));
                $init = mb_strtoupper(mb_substr($e->nombre, 0, 1) . mb_substr($e->apellido ?? '', 0, 1));
                return [
                    'id' => (int) $e->id,
                    'nombre' => (string) $e->nombre,
                    'apellido' => (string) ($e->apellido ?? ''),
                    'fullName' => $fn,
                    'cedula' => (string) ($e->cedula ?? ''),
                    'cargo' => (string) ($e->cargo ?? 'Personal'),
                    'email' => (string) ($e->email ?? ''),
                    'telefono' => (string) ($e->telefono ?? ''),
                    'extension' => (string) ($e->extension ?? ''),
                    'initials' => $init !== '' ? $init : 'EM',
                ];
            })->all();

        // Equipos asignados al departamento
        $equipos = DB::table('equipos')
            ->leftJoin('empleados', 'equipos.empleado_id', '=', 'empleados.id')
            ->select([
                'equipos.*',
                'empleados.nombre as empleado_nombre',
                'empleados.apellido as empleado_apellido',
            ])
            ->where('equipos.departamento_id', $id)
            ->orderBy('equipos.codigo_inventario')
            ->get()
            ->map(function ($eq) {
                $custodio = $eq->empleado_nombre
                    ? trim($eq->empleado_nombre . ' ' . ($eq->empleado_apellido ?? ''))
                    : 'Sin asignar';
                return [
                    'id' => (int) $eq->id,
                    'codigo' => (string) $eq->codigo_inventario,
                    'tipo' => (string) ($eq->tipo ?? 'Equipo'),
                    'marca' => (string) ($eq->marca ?? '-'),
                    'modelo' => (string) ($eq->modelo ?? '-'),
                    'serial' => (string) ($eq->numero_serie ?? '-'),
                    'estado' => (string) ($eq->estado ?? 'disponible'),
                    'ubicacion' => (string) ($eq->ubicacion_fisica ?? ''),
                    'custodio' => $custodio,
                ];
            })->all();

        // Tickets de soporte activos
        $ticketsCount = (int) DB::table('soportes')
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->where(function ($q) use ($id) {
                $q->whereIn('equipo_id', DB::table('equipos')->select('id')->where('departamento_id', $id))
                  ->orWhereIn('empleado_id', DB::table('empleados')->select('id')->where('departamento_id', $id));
            })
            ->count();

        // Consumibles / Inventario asignado
        $consumibles = DB::table('inventario_ubicaciones')
            ->join('inventario_items', 'inventario_ubicaciones.item_id', '=', 'inventario_items.id')
            ->select([
                'inventario_items.id',
                'inventario_items.codigo',
                'inventario_items.nombre',
                'inventario_items.categoria',
                'inventario_items.unidad_medida',
                'inventario_items.stock_minimo',
                'inventario_items.valor_compra',
                'inventario_ubicaciones.cantidad as stock_departamento',
            ])
            ->where('inventario_ubicaciones.departamento_id', $id)
            ->get()
            ->map(function ($it) {
                $qty = (int) $it->stock_departamento;
                $min = (int) ($it->stock_minimo ?? 0);
                $unitVal = (float) ($it->valor_compra ?? 0);
                return [
                    'id' => (int) $it->id,
                    'codigo' => (string) $it->codigo,
                    'nombre' => (string) $it->nombre,
                    'categoria' => (string) ($it->categoria ?? 'General'),
                    'unidad_medida' => (string) ($it->unidad_medida ?? 'uds'),
                    'stock' => $qty,
                    'stock_minimo' => $min,
                    'is_low_stock' => $qty <= $min,
                    'valor_unitario' => $unitVal,
                    'valor_total' => $qty * $unitVal,
                ];
            })->all();

        $consumablesCount = array_sum(array_column($consumibles, 'stock'));
        $consumablesValue = (float) array_sum(array_column($consumibles, 'valor_total'));

        // Candidatos de empleados disponibles para vincular (sin departamento o pertenecientes a otro)
        $candidatosEmpleados = DB::table('empleados')
            ->select(['id', 'nombre', 'apellido', 'cargo', 'departamento_id'])
            ->whereNull('deleted_at')
            ->where(function ($q) use ($id) {
                $q->whereNull('departamento_id')->orWhere('departamento_id', '!=', $id);
            })
            ->orderBy('nombre')
            ->get()
            ->map(fn($e) => [
                'id' => (int) $e->id,
                'nombre' => trim($e->nombre . ' ' . ($e->apellido ?? '')),
                'cargo' => (string) ($e->cargo ?? 'Personal'),
                'departamento_id' => $e->departamento_id ? (int) $e->departamento_id : null,
            ])->all();

        // Candidatos de equipos disponibles para vincular (sin departamento o en estado disponible)
        $candidatosEquipos = DB::table('equipos')
            ->select(['id', 'codigo_inventario', 'tipo', 'marca', 'modelo', 'estado', 'departamento_id'])
            ->where(function ($q) use ($id) {
                $q->whereNull('departamento_id')->orWhere('departamento_id', '!=', $id);
            })
            ->orderBy('codigo_inventario')
            ->get()
            ->map(fn($eq) => [
                'id' => (int) $eq->id,
                'codigo' => (string) $eq->codigo_inventario,
                'nombre' => trim(($eq->tipo ?? 'Equipo') . ' ' . ($eq->marca ?? '') . ' ' . ($eq->modelo ?? '')),
                'estado' => (string) ($eq->estado ?? 'disponible'),
            ])->all();

        $color = DepartmentDetailMapper::getColorForId($id);

        return new DepartmentDetailDTO(
            id: $id,
            code: 'DEPT-' . sprintf('%02d', $id),
            nombre: (string) $row->nombre,
            ubicacion: $row->ubicacion ?? null,
            jefeAreaNombre: $row->jefe_area_nombre ?? null,
            jefeAreaId: isset($row->jefe_area_id) ? (int) $row->jefe_area_id : null,
            descripcion: $row->descripcion ?? null,
            equiposCount: count($equipos),
            empleadosCount: count($empleados),
            inventoryPercent: min(100, max(25, count($equipos) * 3 + 20)),
            color: $color,
            jefeEmail: $jefeEmail,
            jefeTelefono: $jefeTelefono,
            jefeCargo: $jefeCargo,
            jefeInitials: $jefeInitials,
            ticketsCount: $ticketsCount,
            consumablesCount: $consumablesCount,
            consumablesValue: $consumablesValue,
            empleados: $empleados,
            equipos: $equipos,
            consumibles: $consumibles,
            candidatosEmpleados: $candidatosEmpleados,
            candidatosEquipos: $candidatosEquipos,
            createdAt: isset($row->created_at) ? Carbon::parse($row->created_at)->format('d/m/Y') : null,
        );
    }

    public function create(CreateDepartmentDTO $dto): int
    {
        $now = Carbon::now();

        return (int) DB::table('departamentos')->insertGetId([
            'nombre' => $dto->nombre,
            'ubicacion' => $dto->ubicacion,
            'jefe_area_nombre' => $dto->jefeAreaNombre,
            'jefe_area_id' => $dto->jefeAreaId,
            'descripcion' => $dto->descripcion,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
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
        DB::table('equipos')->where('departamento_id', $id)->update(['departamento_id' => null]);
        DB::table('empleados')->where('departamento_id', $id)->update(['departamento_id' => null]);

        $deleted = DB::table('departamentos')->where('id', $id)->delete();
        if ($deleted === 0) {
            throw DepartmentNotFoundException::withId($id);
        }
    }

    public function assignEmployee(int $departmentId, int $employeeId): void
    {
        DB::table('empleados')->where('id', $employeeId)->update([
            'departamento_id' => $departmentId,
            'updated_at' => Carbon::now(),
        ]);
    }

    public function removeEmployee(int $employeeId): void
    {
        DB::table('empleados')->where('id', $employeeId)->update([
            'departamento_id' => null,
            'updated_at' => Carbon::now(),
        ]);
    }

    public function assignEquipment(int $departmentId, int $equipmentId): void
    {
        DB::table('equipos')->where('id', $equipmentId)->update([
            'departamento_id' => $departmentId,
            'updated_at' => Carbon::now(),
        ]);
    }

    public function removeEquipment(int $equipmentId): void
    {
        DB::table('equipos')->where('id', $equipmentId)->update([
            'departamento_id' => null,
            'updated_at' => Carbon::now(),
        ]);
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
