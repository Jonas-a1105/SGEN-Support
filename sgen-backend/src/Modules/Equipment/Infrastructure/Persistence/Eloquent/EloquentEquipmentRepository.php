<?php

declare(strict_types=1);

namespace Modules\Equipment\Infrastructure\Persistence\Eloquent;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Equipment\Application\DTOs\CreateEquipmentDTO;
use Modules\Equipment\Application\DTOs\EquipmentDetailDTO;
use Modules\Equipment\Application\DTOs\EquipmentKpisDTO;
use Modules\Equipment\Application\DTOs\EquipmentListItemDTO;
use Modules\Equipment\Application\DTOs\UpdateEquipmentDTO;
use Modules\Equipment\Application\Mappers\EquipmentDetailMapper;
use Modules\Equipment\Application\Mappers\EquipmentListItemMapper;
use Modules\Equipment\Domain\Enums\EquipmentStatus;
use Modules\Equipment\Domain\Exceptions\EquipmentNotFoundException;
use Modules\Equipment\Domain\Ports\EquipmentRepositoryInterface;

final class EloquentEquipmentRepository implements EquipmentRepositoryInterface
{
    public function getKpis(): EquipmentKpisDTO
    {
        $totalActivos = (int) DB::table('equipos')->count();
        $enReparacion = (int) DB::table('equipos')->where('estado', 'en_reparacion')->count();
        $fueraServicio = (int) DB::table('equipos')->where('estado', 'fuera_de_servicio')->count();
        $operativos = max(0, $totalActivos - $enReparacion - $fueraServicio);

        return new EquipmentKpisDTO(
            totalActivos: $totalActivos,
            operativos: $operativos,
            enReparacion: $enReparacion,
            fueraServicio: $fueraServicio
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return EquipmentListItemDTO[]
     */
    public function list(array $filters = []): array
    {
        $query = DB::table('equipos')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->leftJoin('empleados', 'equipos.empleado_id', '=', 'empleados.id')
            ->select([
                'equipos.*',
                'departamentos.nombre as departamento_nombre',
                'empleados.nombre as empleado_nombre',
                'empleados.apellido as empleado_apellido',
            ])
            ->orderBy('equipos.id', 'desc');

        // Filtro por estado
        if (!empty($filters['estado']) && $filters['estado'] !== 'all') {
            $statusEnum = EquipmentStatus::tryFrom($filters['estado']) ?? EquipmentStatus::fromLabel($filters['estado']);
            $query->where('equipos.estado', $statusEnum->value);
        }

        // Filtro por departamento
        if (!empty($filters['departamento_id'])) {
            $query->where('equipos.departamento_id', (int) $filters['departamento_id']);
        }

        // Filtro por tipo
        if (!empty($filters['tipo'])) {
            $query->where('equipos.tipo', 'ilike', '%' . $filters['tipo'] . '%');
        }

        // Búsqueda general
        if (!empty($filters['search'])) {
            $term = '%' . trim((string) $filters['search']) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('equipos.codigo_inventario', 'ilike', $term)
                    ->orWhere('equipos.numero_serie', 'ilike', $term)
                    ->orWhere('equipos.tipo', 'ilike', $term)
                    ->orWhere('equipos.marca', 'ilike', $term)
                    ->orWhere('equipos.modelo', 'ilike', $term)
                    ->orWhere('equipos.ubicacion_fisica', 'ilike', $term)
                    ->orWhere('departamentos.nombre', 'ilike', $term)
                    ->orWhere('empleados.nombre', 'ilike', $term)
                    ->orWhere('empleados.apellido', 'ilike', $term);
            });
        }

        return $query->get()
            ->map(fn($row) => EquipmentListItemMapper::fromRow($row))
            ->all();
    }

    public function findById(int $id): ?EquipmentDetailDTO
    {
        $row = DB::table('equipos')
            ->leftJoin('departamentos', 'equipos.departamento_id', '=', 'departamentos.id')
            ->leftJoin('empleados', 'equipos.empleado_id', '=', 'empleados.id')
            ->select([
                'equipos.*',
                'departamentos.nombre as departamento_nombre',
                'empleados.nombre as empleado_nombre',
                'empleados.apellido as empleado_apellido',
            ])
            ->where('equipos.id', $id)
            ->first();

        if ($row === null) {
            return null;
        }

        return EquipmentDetailMapper::fromRow($row);
    }

    public function create(CreateEquipmentDTO $dto): int
    {
        $statusEnum = EquipmentStatus::tryFrom($dto->status) ?? EquipmentStatus::fromLabel($dto->status);

        $now = Carbon::now();

        // Determinar código de inventario si no fue provisto
        $code = trim($dto->inventoryCode);
        if ($code === '') {
            $maxId = (int) DB::table('equipos')->max('id');
            $code = sprintf('%05d', $maxId + 1);
        }

        $id = (int) DB::table('equipos')->insertGetId([
            'codigo_inventario' => $code,
            'numero_serie' => $dto->serialNumber,
            'tipo' => $dto->type,
            'marca' => $dto->brand,
            'modelo' => $dto->model,
            'estado' => $statusEnum->value,
            'departamento_id' => $dto->departmentId,
            'empleado_id' => $dto->employeeId,
            'ubicacion_fisica' => $dto->physicalLocation,
            'procesador' => $dto->processor,
            'memoria_ram' => $dto->ram,
            'almacenamiento' => $dto->storage,
            'sistema_operativo' => $dto->os,
            'direccion_ip' => $dto->ipAddress,
            'driver' => $dto->driver,
            'toner' => $dto->toner,
            'fecha_compra' => $dto->purchaseDate,
            'proveedor' => $dto->supplier,
            'garantia' => $dto->warranty,
            'valor_compra' => $dto->purchaseValue,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $id;
    }

    public function update(int $id, UpdateEquipmentDTO $dto): void
    {
        $exists = DB::table('equipos')->where('id', $id)->exists();
        if (!$exists) {
            throw EquipmentNotFoundException::withId($id);
        }

        $payload = ['updated_at' => Carbon::now()];

        if ($dto->inventoryCode !== null) {
            $payload['codigo_inventario'] = $dto->inventoryCode;
        }
        if ($dto->serialNumber !== null) {
            $payload['numero_serie'] = $dto->serialNumber;
        }
        if ($dto->type !== null) {
            $payload['tipo'] = $dto->type;
        }
        if ($dto->brand !== null) {
            $payload['marca'] = $dto->brand;
        }
        if ($dto->model !== null) {
            $payload['modelo'] = $dto->model;
        }
        if ($dto->status !== null) {
            $statusEnum = EquipmentStatus::tryFrom($dto->status) ?? EquipmentStatus::fromLabel($dto->status);
            $payload['estado'] = $statusEnum->value;
        }
        if ($dto->departmentId !== null) {
            $payload['departamento_id'] = $dto->departmentId;
        }
        if ($dto->employeeId !== null) {
            $payload['empleado_id'] = $dto->employeeId;
        }
        if ($dto->physicalLocation !== null) {
            $payload['ubicacion_fisica'] = $dto->physicalLocation;
        }
        if ($dto->processor !== null) {
            $payload['procesador'] = $dto->processor;
        }
        if ($dto->ram !== null) {
            $payload['memoria_ram'] = $dto->ram;
        }
        if ($dto->storage !== null) {
            $payload['almacenamiento'] = $dto->storage;
        }
        if ($dto->os !== null) {
            $payload['sistema_operativo'] = $dto->os;
        }
        if ($dto->ipAddress !== null) {
            $payload['direccion_ip'] = $dto->ipAddress;
        }
        if ($dto->driver !== null) {
            $payload['driver'] = $dto->driver;
        }
        if ($dto->toner !== null) {
            $payload['toner'] = $dto->toner;
        }
        if ($dto->purchaseDate !== null) {
            $payload['fecha_compra'] = $dto->purchaseDate;
        }
        if ($dto->supplier !== null) {
            $payload['proveedor'] = $dto->supplier;
        }
        if ($dto->warranty !== null) {
            $payload['garantia'] = $dto->warranty;
        }
        if ($dto->purchaseValue !== null) {
            $payload['valor_compra'] = $dto->purchaseValue;
        }

        DB::table('equipos')->where('id', $id)->update($payload);
    }

    public function delete(int $id): void
    {
        $deleted = DB::table('equipos')->where('id', $id)->delete();
        if ($deleted === 0) {
            throw EquipmentNotFoundException::withId($id);
        }
    }

    public function getFormOptions(): array
    {
        $departments = DB::table('departamentos')
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->get()
            ->map(fn($d) => ['id' => (int) $d->id, 'nombre' => (string) $d->nombre])
            ->all();

        $employees = DB::table('empleados')
            ->select(['id', 'nombre', 'apellido', 'cargo'])
            ->orderBy('nombre')
            ->get()
            ->map(fn($e) => [
                'id' => (int) $e->id,
                'nombre_completo' => trim($e->nombre . ' ' . ($e->apellido ?? '')),
                'cargo' => $e->cargo,
            ])
            ->all();

        return [
            'departments' => $departments,
            'employees' => $employees,
        ];
    }
}
