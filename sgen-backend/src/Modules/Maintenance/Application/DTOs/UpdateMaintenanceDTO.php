<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\DTOs;

final readonly class UpdateMaintenanceDTO
{
    public function __construct(
        public ?string $fecha = null,
        public ?string $tipoMantenimiento = null,
        public ?string $estado = null,
        public ?string $descripcion = null,
        public ?string $frecuencia = null,
        public ?string $proximaFecha = null,
        public ?float $costo = null,
        public ?int $tecnicoId = null,
        public ?string $observaciones = null,
        public ?int $duracion = null
    ) {}
}
