<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\DTOs;

final class EquipmentKpisDTO
{
    public function __construct(
        public readonly int $totalActivos,
        public readonly int $operativos,
        public readonly int $enReparacion,
        public readonly int $fueraServicio,
        public readonly int $enUso = 0,
        public readonly int $disponibles = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'totalActivos' => $this->totalActivos,
            'operativos' => $this->operativos,
            'enUso' => $this->enUso,
            'disponibles' => $this->disponibles,
            'enReparacion' => $this->enReparacion,
            'fueraServicio' => $this->fueraServicio,
        ];
    }
}
