<?php

declare(strict_types=1);

namespace Modules\Department\Application\Mappers;

use Modules\Department\Application\DTOs\DepartmentListItemDTO;

final class DepartmentListItemMapper
{
    private const COLORS = [
        '#4f46e5', // indigo
        '#f97316', // naranja
        '#ec4899', // rosa
        '#10b981', // esmeralda
        '#06b6d4', // cian
        '#8b5cf6', // morado
        '#f59e0b', // ambar
        '#f43f5e', // carmesí
    ];

    /**
     * @param object|array<string, mixed> $row
     */
    public static function fromRow(object|array $row): DepartmentListItemDTO
    {
        $data = (array) $row;
        $id = (int) ($data['id'] ?? 0);

        $code = 'DEPT-' . sprintf('%02d', $id);
        $name = (string) ($data['nombre'] ?? 'Sin Nombre');
        $location = (string) ($data['ubicacion'] ?? 'Edificio Central');
        $desc = (string) ($data['descripcion'] ?? "Gestión y administración del área de {$name}.");
        if (trim($desc) === '') {
            $desc = "Gestión y administración del área de {$name}.";
        }

        $equipos = (int) ($data['equipos_count'] ?? 0);
        $empleados = (int) ($data['empleados_count'] ?? 0);

        // Porcentaje inventario estimado o basado en equipos (máx 100%)
        $inventoryPercent = (int) ($data['inventory_percent'] ?? min(100, max(25, $equipos * 3 + 20)));

        $color = self::COLORS[$id % count(self::COLORS)];

        $manager = null;
        if (!empty($data['jefe_area_nombre'])) {
            $manager = (string) $data['jefe_area_nombre'];
        }

        return new DepartmentListItemDTO(
            numericId: $id,
            id: 'dept_' . sprintf('%02d', $id),
            code: $code,
            name: $name,
            desc: $desc,
            location: $location,
            equipos: $equipos,
            empleados: $empleados,
            inventoryPercent: $inventoryPercent,
            color: $color,
            manager: $manager,
            managerId: isset($data['jefe_area_id']) ? (int) $data['jefe_area_id'] : null,
        );
    }
}
