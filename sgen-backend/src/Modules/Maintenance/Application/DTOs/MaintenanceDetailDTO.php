<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\DTOs;

final readonly class MaintenanceDetailDTO
{
    public function __construct(
        public int $id,
        public int $equipoId,
        public string $fecha,
        public string $tipoMantenimiento,
        public string $estado,
        public string $descripcion,
        public string $frecuencia,
        public ?string $proximaFecha = null,
        public ?float $costo = null,
        public ?int $tecnicoId = null,
        public ?string $tecnicoNombre = null,
        public ?string $realizadoPor = null,
        public ?string $observaciones = null,
        public ?array $checklist = null,
        public ?int $duracion = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?array $equipo = null
    ) {}
}
