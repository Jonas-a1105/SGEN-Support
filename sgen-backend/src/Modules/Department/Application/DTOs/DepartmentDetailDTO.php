<?php

declare(strict_types=1);

namespace Modules\Department\Application\DTOs;

final class DepartmentDetailDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $nombre,
        public readonly ?string $ubicacion,
        public readonly ?string $jefeAreaNombre,
        public readonly ?int $jefeAreaId,
        public readonly ?string $descripcion,
        public readonly int $equiposCount,
        public readonly int $empleadosCount,
        public readonly int $inventoryPercent,
        public readonly string $color,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'nombre' => $this->nombre,
            'ubicacion' => $this->ubicacion,
            'jefeAreaNombre' => $this->jefeAreaNombre,
            'jefeAreaId' => $this->jefeAreaId,
            'descripcion' => $this->descripcion,
            'equiposCount' => $this->equiposCount,
            'empleadosCount' => $this->empleadosCount,
            'inventoryPercent' => $this->inventoryPercent,
            'color' => $this->color,
        ];
    }
}
