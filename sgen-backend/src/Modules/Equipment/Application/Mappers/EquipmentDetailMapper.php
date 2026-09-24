<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\Mappers;

use Modules\Equipment\Application\DTOs\EquipmentDetailDTO;
use Modules\Equipment\Domain\Enums\EquipmentStatus;
use Modules\Equipment\Domain\Enums\EquipmentType;

final class EquipmentDetailMapper
{
    /**
     * @param object|array<string, mixed> $row
     */
    public static function fromRow(object|array $row): EquipmentDetailDTO
    {
        $data = (array) $row;

        $rawStatus = (string) ($data['estado'] ?? 'disponible');
        $statusEnum = EquipmentStatus::tryFrom($rawStatus) ?? EquipmentStatus::fromLabel($rawStatus);

        $rawType = (string) ($data['tipo'] ?? 'Computadora');
        $typeEnum = EquipmentType::fromString($rawType);

        $name = trim(($data['marca'] ?? '') . ' ' . ($data['modelo'] ?? ''));
        if ($name === '') {
            $name = (string) ($data['nombre'] ?? $typeEnum->normalized());
        }

        $employeeName = null;
        if (!empty($data['empleado_nombre'])) {
            $employeeName = trim($data['empleado_nombre'] . ' ' . ($data['empleado_apellido'] ?? ''));
        }

        return new EquipmentDetailDTO(
            id: (int) ($data['id'] ?? 0),
            inventoryCode: (string) ($data['codigo_inventario'] ?? sprintf('%05d', (int) ($data['id'] ?? 0))),
            serialNumber: (string) ($data['numero_serie'] ?? ''),
            name: $name,
            type: $typeEnum->normalized(),
            brand: $data['marca'] ?? null,
            model: $data['modelo'] ?? null,
            status: $statusEnum->label(),
            rawStatus: $rawStatus,
            departmentId: isset($data['departamento_id']) ? (int) $data['departamento_id'] : null,
            departmentName: $data['departamento_nombre'] ?? null,
            employeeId: isset($data['empleado_id']) ? (int) $data['empleado_id'] : null,
            employeeName: $employeeName,
            physicalLocation: $data['ubicacion_fisica'] ?? null,
            processor: $data['procesador'] ?? null,
            ram: $data['memoria_ram'] ?? null,
            storage: $data['almacenamiento'] ?? null,
            os: $data['sistema_operativo'] ?? null,
            ipAddress: $data['direccion_ip'] ?? null,
            driver: $data['driver'] ?? null,
            toner: $data['toner'] ?? null,
            purchaseDate: $data['fecha_compra'] ?? null,
            supplier: $data['proveedor'] ?? null,
            warranty: $data['garantia'] ?? null,
            purchaseValue: isset($data['valor_compra']) ? (float) $data['valor_compra'] : null,
        );
    }
}
