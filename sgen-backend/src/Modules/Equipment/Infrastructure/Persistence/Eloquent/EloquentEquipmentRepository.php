<?php

declare(strict_types=1);

namespace Modules\Equipment\Infrastructure\Persistence\Eloquent;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Equipment\Application\DTOs\CreateEquipmentDTO;
use Modules\Equipment\Application\DTOs\EquipmentDetailDTO;
use Modules\Equipment\Application\DTOs\EquipmentKpisDTO;
use Modules\Equipment\Application\DTOs\EquipmentListItemDTO;
use Modules\Equipment\Application\DTOs\RegisterEquipmentDTO;
use Modules\Equipment\Application\DTOs\TransferEquipmentDTO;
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
        $enUso = (int) DB::table('equipos')->where('estado', 'en_uso')->count();
        $disponibles = (int) DB::table('equipos')->whereIn('estado', ['disponible', 'nuevo', 'en_reserva'])->count();
        $enReparacion = (int) DB::table('equipos')->where('estado', 'en_reparacion')->count();
        $fueraServicio = (int) DB::table('equipos')->whereIn('estado', ['fuera_de_servicio', 'baja'])->count();
        $operativos = max(0, $totalActivos - $enReparacion - $fueraServicio);

        return new EquipmentKpisDTO(
            totalActivos: $totalActivos,
            operativos: $operativos,
            enReparacion: $enReparacion,
            fueraServicio: $fueraServicio,
            enUso: $enUso,
            disponibles: $disponibles,
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

    public function getCompleteDetail(int $id): ?EquipmentDetailDTO
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

        // Warranty calculation
        $warrantyPercent = 0;
        $warrantyStatus = 'expired';
        $warrantyRemaining = null;

        if (!empty($row->fecha_compra) && !empty($row->garantia)) {
            $start = Carbon::parse($row->fecha_compra)->timestamp;
            $end = Carbon::parse($row->garantia)->timestamp;
            $now = Carbon::now()->timestamp;
            $total = $end - $start;
            $elapsed = $now - $start;

            if ($total > 0) {
                $warrantyPercent = (int) max(0, min(100, (($total - $elapsed) / $total) * 100));
                if ($now < $end) {
                    $warrantyStatus = $warrantyPercent > 33 ? 'active' : 'warning';
                    $daysRemaining = (int) ceil(($end - $now) / 86400);
                    $warrantyRemaining = $daysRemaining > 365
                        ? round($daysRemaining / 365, 1) . ' años'
                        : round($daysRemaining / 30) . ' meses';
                }
            }
        }

        // Associated support tickets
        $tickets = DB::table('soportes')
            ->select(['id', 'titulo', 'descripcion', 'estado', 'prioridad', 'fecha'])
            ->where('equipo_id', $id)
            ->orderByDesc('fecha')
            ->limit(20)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => (int) $s->id,
                    'titulo' => (string) ($s->titulo ?? 'Ticket #' . $s->id),
                    'descripcion' => (string) ($s->descripcion ?? ''),
                    'estado' => (string) ($s->estado ?? 'pendiente'),
                    'prioridad' => (string) ($s->prioridad ?? 'media'),
                    'fecha' => isset($s->fecha) ? Carbon::parse($s->fecha)->format('d/m/Y H:i') : null,
                ];
            })
            ->all();

        // Associated maintenance orders
        $maintenances = DB::table('mantenimientos')
            ->select(['id', 'tipo_mantenimiento', 'estado', 'descripcion', 'costo', 'realizado_por', 'fecha', 'proxima_fecha'])
            ->where('equipo_id', $id)
            ->orderByDesc('fecha')
            ->limit(20)
            ->get()
            ->map(function ($m) {
                return [
                    'id' => (int) $m->id,
                    'tipo' => (string) ($m->tipo_mantenimiento ?? 'preventivo'),
                    'estado' => (string) ($m->estado ?? 'completado'),
                    'descripcion' => (string) ($m->descripcion ?? ''),
                    'costo' => (float) ($m->costo ?? 0),
                    'realizadoPor' => (string) ($m->realizado_por ?? 'Técnico de soporte'),
                    'fecha' => isset($m->fecha) ? Carbon::parse($m->fecha)->format('d/m/Y') : null,
                    'proximaFecha' => isset($m->proxima_fecha) ? Carbon::parse($m->proxima_fecha)->format('d/m/Y') : null,
                ];
            })
            ->all();

        // Department options
        $departamentos = DB::table('departamentos')
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->get()
            ->map(fn($d) => ['id' => (int) $d->id, 'nombre' => (string) $d->nombre])
            ->all();

        // Employee options
        $empleados = DB::table('empleados')
            ->select(['id', 'nombre', 'apellido', 'cargo'])
            ->whereNull('deleted_at')
            ->orderBy('nombre')
            ->get()
            ->map(fn($e) => [
                'id' => (int) $e->id,
                'nombre' => trim($e->nombre . ' ' . ($e->apellido ?? '')),
                'cargo' => (string) ($e->cargo ?? 'Personal'),
            ])
            ->all();

        $baseDto = EquipmentDetailMapper::fromRow($row);

        return new EquipmentDetailDTO(
            id: $baseDto->id,
            inventoryCode: $baseDto->inventoryCode,
            serialNumber: $baseDto->serialNumber,
            name: $baseDto->name,
            type: $baseDto->type,
            brand: $baseDto->brand,
            model: $baseDto->model,
            status: $baseDto->status,
            rawStatus: $baseDto->rawStatus,
            departmentId: $baseDto->departmentId,
            departmentName: $baseDto->departmentName,
            employeeId: $baseDto->employeeId,
            employeeName: $baseDto->employeeName,
            physicalLocation: $baseDto->physicalLocation,
            processor: $baseDto->processor,
            ram: $baseDto->ram,
            storage: $baseDto->storage,
            os: $baseDto->os,
            ipAddress: $baseDto->ipAddress,
            driver: $baseDto->driver,
            toner: $baseDto->toner,
            purchaseDate: $baseDto->purchaseDate,
            supplier: $baseDto->supplier,
            warranty: $baseDto->warranty,
            purchaseValue: $baseDto->purchaseValue,
            warrantyPercent: $warrantyPercent,
            warrantyStatus: $warrantyStatus,
            warrantyRemaining: $warrantyRemaining,
            tickets: $tickets,
            maintenances: $maintenances,
            departamentos: $departamentos,
            empleados: $empleados,
        );
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

        // Domain-level duplicate check for inventory code and serial number
        $duplicate = DB::table('equipos')
            ->where(function ($q) use ($code, $dto) {
                $q->where('codigo_inventario', $code);
                if (!empty($dto->serialNumber)) {
                    $q->orWhere('numero_serie', $dto->serialNumber);
                }
            })
            ->exists();

        if ($duplicate) {
            throw new \RuntimeException(
                "Ya existe un equipo con el código '{$code}'"
                . (!empty($dto->serialNumber) ? " o el serial '{$dto->serialNumber}'" : '') . '.'
            );
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

    public function register(RegisterEquipmentDTO $dto): int
    {
        return $this->create(CreateEquipmentDTO::fromArray([
            'codigo_inventario' => $dto->codigoInventario,
            'tipo' => $dto->tipo,
            'marca' => $dto->marca,
            'modelo' => $dto->modelo,
            'estado' => $dto->estado,
            'numero_serie' => $dto->numeroSerie,
            'procesador' => $dto->procesador,
            'memoria_ram' => $dto->memoriaRam,
            'almacenamiento' => $dto->almacenamiento,
            'sistema_operativo' => $dto->sistemaOperativo,
            'direccion_ip' => $dto->direccionIp,
            'departamento_id' => $dto->departamentoId,
            'empleado_id' => $dto->empleadoId,
            'ubicacion_fisica' => $dto->ubicacionFisica,
            'valor_compra' => $dto->valorCompra,
            'proveedor' => $dto->proveedor,
        ]));
    }

    public function transfer(TransferEquipmentDTO $dto): void
    {
        $updated = DB::table('equipos')
            ->where('id', $dto->equipoId)
            ->where('departamento_id', $dto->departamentoOrigenId)
            ->update([
                'departamento_id' => $dto->departamentoDestinoId,
                'updated_at' => Carbon::now(),
            ]);

        if ($updated === 0) {
            $equipo = DB::table('equipos')->where('id', $dto->equipoId)->first();

            if ($equipo === null) {
                throw EquipmentNotFoundException::withId($dto->equipoId);
            }

            throw new \RuntimeException(
                "El equipo #{$dto->equipoId} no pertenece al departamento de origen #{$dto->departamentoOrigenId}."
            );
        }
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
        if ($dto->hasDepartmentId) {
            $payload['departamento_id'] = $dto->departmentId;
        } elseif ($dto->departmentId !== null) {
            $payload['departamento_id'] = $dto->departmentId;
        }
        if ($dto->hasEmployeeId) {
            $payload['empleado_id'] = $dto->employeeId;
        } elseif ($dto->employeeId !== null) {
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
