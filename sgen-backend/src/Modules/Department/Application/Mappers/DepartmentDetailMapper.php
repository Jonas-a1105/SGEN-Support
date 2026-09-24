<?php

declare(strict_types=1);

namespace Modules\Department\Application\Mappers;

use Modules\Department\Application\DTOs\DepartmentDetailDTO;

final class DepartmentDetailMapper
{
    private const COLORS = [
        '#4f46e5', '#f97316', '#ec4899', '#10b981', '#06b6d4', '#8b5cf6', '#f59e0b', '#f43f5e'
    ];

    /**
     * @param object|array<string, mixed> $row
     */
    public static function fromRow(object|array $row): DepartmentDetailDTO
    {
        $data = (array) $row;
        $id = (int) ($data['id'] ?? 0);

        $name = (string) ($data['nombre'] ?? '');
        $equipos = (int) ($data['equipos_count'] ?? 0);
        $empleados = (int) ($data['empleados_count'] ?? 0);
        $inventoryPercent = (int) ($data['inventory_percent'] ?? min(100, max(25, $equipos * 3 + 20)));
        $color = self::COLORS[$id % count(self::COLORS)];

        return new DepartmentDetailDTO(
            id: $id,
            code: 'DEPT-' . sprintf('%02d', $id),
            nombre: $name,
            ubicacion: $data['ubicacion'] ?? null,
            jefeAreaNombre: $data['jefe_area_nombre'] ?? null,
            jefeAreaId: isset($data['jefe_area_id']) ? (int) $data['jefe_area_id'] : null,
            descripcion: $data['descripcion'] ?? null,
            equiposCount: $equipos,
            empleadosCount: $empleados,
            inventoryPercent: $inventoryPercent,
            color: $color,
        );
    }
}
