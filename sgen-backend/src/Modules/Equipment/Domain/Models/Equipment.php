<?php

declare(strict_types=1);

namespace Modules\Equipment\Domain\Models;

use InvalidArgumentException;
use Modules\Equipment\Domain\Enums\EquipmentStatus;
use Modules\Equipment\Domain\Enums\EquipmentType;

final class Equipment
{
    public function __construct(
        private readonly ?int $id,
        private string $inventoryCode,
        private string $serialNumber,
        private EquipmentType $type,
        private ?string $brand,
        private ?string $model,
        private EquipmentStatus $status,
        private ?int $departmentId = null,
        private ?int $employeeId = null,
        private ?string $physicalLocation = null,
        private ?string $processor = null,
        private ?string $ram = null,
        private ?string $storage = null,
        private ?string $os = null,
        private ?string $ipAddress = null,
        private ?string $driver = null,
        private ?string $toner = null,
        private ?string $purchaseDate = null,
        private ?string $supplier = null,
        private ?string $warranty = null,
        private ?float $purchaseValue = null,
    ) {
        $this->ensureInvariants();
    }

    private function ensureInvariants(): void
    {
        if (trim($this->inventoryCode) === '') {
            throw new InvalidArgumentException('El código de inventario no puede estar vacío.');
        }

        if (trim($this->serialNumber) === '') {
            throw new InvalidArgumentException('El número de serie no puede estar vacío.');
        }
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function inventoryCode(): string
    {
        return $this->inventoryCode;
    }

    public function serialNumber(): string
    {
        return $this->serialNumber;
    }

    public function type(): EquipmentType
    {
        return $this->type;
    }

    public function brand(): ?string
    {
        return $this->brand;
    }

    public function model(): ?string
    {
        return $this->model;
    }

    public function status(): EquipmentStatus
    {
        return $this->status;
    }

    public function departmentId(): ?int
    {
        return $this->departmentId;
    }

    public function employeeId(): ?int
    {
        return $this->employeeId;
    }

    public function physicalLocation(): ?string
    {
        return $this->physicalLocation;
    }

    public function processor(): ?string
    {
        return $this->processor;
    }

    public function ram(): ?string
    {
        return $this->ram;
    }

    public function storage(): ?string
    {
        return $this->storage;
    }

    public function os(): ?string
    {
        return $this->os;
    }

    public function ipAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function driver(): ?string
    {
        return $this->driver;
    }

    public function toner(): ?string
    {
        return $this->toner;
    }

    public function purchaseDate(): ?string
    {
        return $this->purchaseDate;
    }

    public function supplier(): ?string
    {
        return $this->supplier;
    }

    public function warranty(): ?string
    {
        return $this->warranty;
    }

    public function purchaseValue(): ?float
    {
        return $this->purchaseValue;
    }

    public function assignToDepartment(int $departmentId, ?string $location = null): void
    {
        $this->departmentId = $departmentId;
        if ($location !== null) {
            $this->physicalLocation = $location;
        }
    }

    public function assignToEmployee(?int $employeeId): void
    {
        $this->employeeId = $employeeId;
        if ($employeeId !== null && $this->status === EquipmentStatus::DISPONIBLE) {
            $this->status = EquipmentStatus::EN_USO;
        }
    }

    public function changeStatus(EquipmentStatus $newStatus): void
    {
        $this->status = $newStatus;
        if ($newStatus === EquipmentStatus::FUERA_DE_SERVICIO || $newStatus === EquipmentStatus::DISPONIBLE) {
            $this->employeeId = null;
        }
    }

    public function isOperational(): bool
    {
        return $this->status !== EquipmentStatus::FUERA_DE_SERVICIO && $this->status !== EquipmentStatus::EN_REPARACION;
    }
}
