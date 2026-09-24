<?php

declare(strict_types=1);

namespace Modules\Category\Application\Mappers;

use Modules\Category\Application\DTOs\CategoryListItemDTO;

final class CategoryListItemMapper
{
    /**
     * @param  object|array<string, mixed>  $row
     */
    public static function fromRow(object|array $row): CategoryListItemDTO
    {
        $id = is_object($row) ? (int) $row->id : (int) $row['id'];
        $nombre = is_object($row) ? (string) $row->nombre : (string) $row['nombre'];
        $descripcion = is_object($row) ? ($row->descripcion ?? null) : ($row['descripcion'] ?? null);
        $icono = is_object($row) ? (string) ($row->icono ?? 'hardware') : (string) ($row['icono'] ?? 'hardware');
        $color = is_object($row) ? (string) ($row->color ?? '#0d6efd') : (string) ($row['color'] ?? '#0d6efd');
        $activo = is_object($row) ? (bool) ($row->activo ?? true) : (bool) ($row['activo'] ?? true);
        $totalTickets = is_object($row) ? (int) ($row->total_tickets ?? 0) : (int) ($row['total_tickets'] ?? 0);

        return new CategoryListItemDTO(
            id: $id,
            nombre: $nombre,
            descripcion: $descripcion !== null ? (string) $descripcion : null,
            icono: $icono,
            color: $color,
            activo: $activo,
            totalTickets: $totalTickets
        );
    }
}
