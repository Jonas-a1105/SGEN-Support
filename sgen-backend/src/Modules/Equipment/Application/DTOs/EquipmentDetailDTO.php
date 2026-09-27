<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\DTOs;

final class EquipmentDetailDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $inventoryCode,
        public readonly string $serialNumber,
        public readonly string $name,
        public readonly string $type,
        public readonly ?string $brand,
        public readonly ?string $model,
        public readonly string $status,
        public readonly string $rawStatus,
        public readonly ?int $departmentId,
        public readonly ?string $departmentName,
        public readonly ?int $employeeId,
        public readonly ?string $employeeName,
        public readonly ?string $physicalLocation,
        public readonly ?string $processor,
        public readonly ?string $ram,
        public readonly ?string $storage,
        public readonly ?string $os,
        public readonly ?string $ipAddress,
        public readonly ?string $driver,
        public readonly ?string $toner,
        public readonly ?string $purchaseDate,
        public readonly ?string $supplier,
        public readonly ?string $warranty,
        public readonly ?float $purchaseValue,
        public readonly int $warrantyPercent = 0,
        public readonly string $warrantyStatus = 'expired',
        public readonly ?string $warrantyRemaining = null,
        /** @var list<array<string, mixed>> */
        public readonly array $tickets = [],
        /** @var list<array<string, mixed>> */
        public readonly array $maintenances = [],
        /** @var array<string, mixed>|null */
        public readonly ?array $custodiaActual = null,
        /** @var list<array<string, mixed>> */
        public readonly array $custodias = [],
        public readonly array $departamentos = [],
        public readonly array $empleados = [],
        // #22: versión vista por el lector; un 409 la protege de pisar cambios.
        public readonly int $version = 1,
        public readonly ?string $motivoBaja = null,
        public readonly ?string $fechaBaja = null,
        public readonly ?float $valorRecuperacion = null,
        public readonly ?string $destinoBaja = null,
        public readonly ?string $notaBaja = null,
        public readonly ?string $responsableNombre = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'inventoryCode' => $this->inventoryCode,
            'serialNumber' => $this->serialNumber,
            'name' => $this->name,
            'type' => $this->type,
            'brand' => $this->brand,
            'model' => $this->model,
            'status' => $this->status,
            'rawStatus' => $this->rawStatus,
            'departmentId' => $this->departmentId,
            'departmentName' => $this->departmentName,
            'employeeId' => $this->employeeId,
            'employeeName' => $this->employeeName,
            'physicalLocation' => $this->physicalLocation,
            'processor' => $this->processor,
            'ram' => $this->ram,
            'storage' => $this->storage,
            'os' => $this->os,
            'ipAddress' => $this->ipAddress,
            'driver' => $this->driver,
            'toner' => $this->toner,
            'purchaseDate' => $this->purchaseDate,
            'supplier' => $this->supplier,
            'warranty' => $this->warranty,
            'purchaseValue' => $this->purchaseValue,
            'motivo_baja' => $this->motivoBaja,
            'fecha_baja' => $this->fechaBaja,
            'valor_recuperacion' => $this->valorRecuperacion,
            'destino_baja' => $this->destinoBaja,
            'nota_baja' => $this->notaBaja,
            'responsable_nombre' => $this->responsableNombre,
            'warrantyPercent' => $this->warrantyPercent,
            'warrantyStatus' => $this->warrantyStatus,
            'warrantyRemaining' => $this->warrantyRemaining,
            'tickets' => $this->tickets,
            'maintenances' => $this->maintenances,
            'custodiaActual' => $this->custodiaActual,
            'custodias' => $this->custodias,
            'departamentos' => $this->departamentos,
            'empleados' => $this->empleados,
        ];
    }
}
