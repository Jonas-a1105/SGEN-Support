<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\Mappers;

use Modules\Maintenance\Application\DTOs\MaintenanceListItemDTO;
use stdClass;

final class MaintenanceListItemMapper
{
    public static function fromDatabaseRow(object $row): MaintenanceListItemDTO
    {
        return new MaintenanceListItemDTO(
            id: (int) $row->id,
            fecha: (string) $row->fecha,
            tipoMantenimiento: (string) $row->tipo_mantenimiento,
            estado: (string) $row->estado,
            descripcion: (string) $row->descripcion,
            frecuencia: (string) $row->frecuencia,
            proximaFecha: $row->proxima_fecha ?? null,
            costo: $row->costo !== null ? (float) $row->costo : null,
            equipoCodigo: $row->equipo_codigo ?? null,
            equipoTipo: $row->equipo_tipo ?? null,
            tecnicoNombre: $row->tecnico_nombre ?? null
        );
    }
}
