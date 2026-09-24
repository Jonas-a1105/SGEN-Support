<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\DTOs;

final readonly class MaintenanceListItemDTO
{
    public function __construct(
        public int $id,
        public string $fecha,
        public string $tipoMantenimiento,
        public string $estado,
        public string $descripcion,
        public string $frecuencia,
        public ?string $proximaFecha = null,
        public ?float $costo = null,
        public ?string $equipoCodigo = null,
        public ?string $equipoTipo = null,
        public ?string $tecnicoNombre = null
    ) {}
}
