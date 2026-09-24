<?php

declare(strict_types=1);

namespace Modules\Department\Application\DTOs;

final class DepartmentDetailDTO
{
    /**
     * @param array<int, array<string, mixed>> $empleados
     * @param array<int, array<string, mixed>> $equipos
     * @param array<int, array<string, mixed>> $consumibles
     * @param array<int, array<string, mixed>> $candidatosEmpleados
     * @param array<int, array<string, mixed>> $candidatosEquipos
     */
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
        public readonly ?string $jefeEmail = null,
        public readonly ?string $jefeTelefono = null,
        public readonly ?string $jefeCargo = null,
        public readonly string $jefeInitials = '--',
        public readonly int $ticketsCount = 0,
        public readonly int $consumablesCount = 0,
        public readonly float $consumablesValue = 0.0,
        public readonly array $empleados = [],
        public readonly array $equipos = [],
        public readonly array $consumibles = [],
        public readonly array $candidatosEmpleados = [],
        public readonly array $candidatosEquipos = [],
        public readonly ?string $createdAt = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'nombre' => $this->nombre,
            'ubicacion' => $this->ubicacion,
            'jefeAreaNombre' => $this->jefeAreaNombre,
            'jefeAreaId' => $this->jefeAreaId,
            'jefeEmail' => $this->jefeEmail,
            'jefeTelefono' => $this->jefeTelefono,
            'jefeCargo' => $this->jefeCargo,
            'jefeInitials' => $this->jefeInitials,
            'descripcion' => $this->descripcion,
            'equiposCount' => $this->equiposCount,
            'empleadosCount' => $this->empleadosCount,
            'inventoryPercent' => $this->inventoryPercent,
            'ticketsCount' => $this->ticketsCount,
            'consumablesCount' => $this->consumablesCount,
            'consumablesValue' => $this->consumablesValue,
            'color' => $this->color,
            'empleados' => $this->empleados,
            'equipos' => $this->equipos,
            'consumibles' => $this->consumibles,
            'candidatosEmpleados' => $this->candidatosEmpleados,
            'candidatosEquipos' => $this->candidatosEquipos,
            'createdAt' => $this->createdAt,
        ];
    }
}
