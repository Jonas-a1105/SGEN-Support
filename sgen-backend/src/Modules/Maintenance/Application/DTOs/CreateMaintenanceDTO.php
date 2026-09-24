<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\DTOs;

final readonly class CreateMaintenanceDTO
{
    public function __construct(
        public int $equipoId,
        public string $fecha,
        public string $tipoMantenimiento,
        public string $descripcion,
        public string $frecuencia = 'unica',
        public ?string $proximaFecha = null,
        public ?float $costo = null,
        public ?int $tecnicoId = null,
        public ?string $observaciones = null,
        public ?int $duracion = null
    ) {}
}
