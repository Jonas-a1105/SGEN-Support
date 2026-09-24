<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\Mappers;

use Modules\Equipment\Application\DTOs\EquipmentListItemDTO;
use Modules\Equipment\Domain\Enums\EquipmentStatus;
use Modules\Equipment\Domain\Enums\EquipmentType;

final class EquipmentListItemMapper
{
    /**
     * @param object|array<string, mixed> $row
     */
    public static function fromRow(object|array $row): EquipmentListItemDTO
    {
        $data = (array) $row;

        $rawStatus = (string) ($data['estado'] ?? 'disponible');
        $statusEnum = EquipmentStatus::tryFrom($rawStatus) ?? EquipmentStatus::fromLabel($rawStatus);
        $statusLabel = $statusEnum->label();

        $rawType = (string) ($data['tipo'] ?? 'Computadora');
        $typeEnum = EquipmentType::fromString($rawType);
        $typeName = $typeEnum->normalized();

        $name = trim(($data['marca'] ?? '') . ' ' . ($data['modelo'] ?? ''));
        if ($name === '') {
            $name = (string) ($data['nombre'] ?? $typeName);
        }

        $idFormatted = (string) ($data['codigo_inventario'] ?? sprintf('%05d', (int) ($data['id'] ?? 0)));

        $dept = (string) ($data['departamento_nombre'] ?? $data['ubicacion_fisica'] ?? 'Sin Departamento');
        $deptShort = mb_strlen($dept) > 20 ? mb_substr($dept, 0, 18) . '...' : $dept;

        $assignedTo = null;
        if (!empty($data['empleado_nombre'])) {
            $assignedTo = trim($data['empleado_nombre'] . ' ' . ($data['empleado_apellido'] ?? ''));
        }

        return new EquipmentListItemDTO(
            numericId: (int) ($data['id'] ?? 0),
            id: $idFormatted,
            name: $name,
            type: $typeName,
            dept: $dept,
            deptShort: $deptShort,
            status: $statusLabel,
            rawStatus: $rawStatus,
            serialNumber: $data['numero_serie'] ?? null,
            assignedTo: $assignedTo,
            location: $data['ubicacion_fisica'] ?? null,
            ipAddress: $data['direccion_ip'] ?? null,
        );
    }
}
