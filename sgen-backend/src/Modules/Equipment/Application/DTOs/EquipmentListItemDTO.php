<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\DTOs;

final class EquipmentListItemDTO
{
    public function __construct(
        public readonly int $numericId,
        public readonly string $id,
        public readonly string $name,
        public readonly string $type,
        public readonly string $dept,
        public readonly string $deptShort,
        public readonly string $status,
        public readonly string $rawStatus,
        public readonly ?string $serialNumber = null,
        public readonly ?string $assignedTo = null,
        public readonly ?string $location = null,
        public readonly ?string $ipAddress = null,
        public readonly ?int $departmentId = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'numericId' => $this->numericId,
            'name' => $this->name,
            'type' => $this->type,
            'dept' => $this->dept,
            'deptShort' => $this->deptShort,
            'status' => $this->status,
            'rawStatus' => $this->rawStatus,
            'serialNumber' => $this->serialNumber,
            'assignedTo' => $this->assignedTo,
            'location' => $this->location,
            'ipAddress' => $this->ipAddress,
            'departmentId' => $this->departmentId,
        ];
    }
}
