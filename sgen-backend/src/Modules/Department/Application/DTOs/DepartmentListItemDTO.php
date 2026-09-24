<?php

declare(strict_types=1);

namespace Modules\Department\Application\DTOs;

final class DepartmentListItemDTO
{
    public function __construct(
        public readonly int $numericId,
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        public readonly string $desc,
        public readonly string $location,
        public readonly int $equipos,
        public readonly int $empleados,
        public readonly int $inventoryPercent,
        public readonly string $color,
        public readonly ?string $manager,
        public readonly ?int $managerId = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'numericId' => $this->numericId,
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'desc' => $this->desc,
            'location' => $this->location,
            'equipos' => $this->equipos,
            'empleados' => $this->empleados,
            'inventoryPercent' => $this->inventoryPercent,
            'color' => $this->color,
            'manager' => $this->manager,
            'managerId' => $this->managerId,
        ];
    }
}
