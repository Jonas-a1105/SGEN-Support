<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\DTOs;

final class CreateEquipmentDTO
{
    public function __construct(
        public readonly string $inventoryCode,
        public readonly string $serialNumber,
        public readonly string $type,
        public readonly ?string $brand = null,
        public readonly ?string $model = null,
        public readonly string $status = 'en_uso',
        public readonly ?int $departmentId = null,
        public readonly ?int $employeeId = null,
        public readonly ?string $physicalLocation = null,
        public readonly ?string $processor = null,
        public readonly ?string $ram = null,
        public readonly ?string $storage = null,
        public readonly ?string $os = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $driver = null,
        public readonly ?string $toner = null,
        public readonly ?string $purchaseDate = null,
        public readonly ?string $supplier = null,
        public readonly ?string $warranty = null,
        public readonly ?float $purchaseValue = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            inventoryCode: (string) ($data['codigo_inventario'] ?? $data['id'] ?? ''),
            serialNumber: (string) ($data['numero_serie'] ?? $data['serialNumber'] ?? ('SN-' . time() . '-' . rand(100, 999))),
            type: (string) ($data['tipo'] ?? $data['type'] ?? 'Computadora'),
            brand: isset($data['marca']) ? (string) $data['marca'] : null,
            model: isset($data['modelo']) ? (string) $data['modelo'] : ($data['nombre'] ?? null),
            status: (string) ($data['estado'] ?? $data['status'] ?? 'en_uso'),
            departmentId: isset($data['departamento_id']) ? (int) $data['departamento_id'] : null,
            employeeId: isset($data['empleado_id']) ? (int) $data['empleado_id'] : null,
            physicalLocation: isset($data['ubicacion_fisica']) ? (string) $data['ubicacion_fisica'] : null,
            processor: isset($data['procesador']) ? (string) $data['procesador'] : null,
            ram: isset($data['memoria_ram']) ? (string) $data['memoria_ram'] : null,
            storage: isset($data['almacenamiento']) ? (string) $data['almacenamiento'] : null,
            os: isset($data['sistema_operativo']) ? (string) $data['sistema_operativo'] : null,
            ipAddress: isset($data['direccion_ip']) ? (string) $data['direccion_ip'] : null,
            driver: isset($data['driver']) ? (string) $data['driver'] : null,
            toner: isset($data['toner']) ? (string) $data['toner'] : null,
            purchaseDate: isset($data['fecha_compra']) ? (string) $data['fecha_compra'] : null,
            supplier: isset($data['proveedor']) ? (string) $data['proveedor'] : null,
            warranty: isset($data['garantia']) ? (string) $data['garantia'] : null,
            purchaseValue: isset($data['valor_compra']) ? (float) $data['valor_compra'] : null,
        );
    }
}
