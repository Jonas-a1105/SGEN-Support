<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\DTOs;

final class UpdateEquipmentDTO
{
    public function __construct(
        public readonly ?string $inventoryCode = null,
        public readonly ?string $serialNumber = null,
        public readonly ?string $type = null,
        public readonly ?string $brand = null,
        public readonly ?string $model = null,
        public readonly ?string $status = null,
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
        public readonly bool $hasDepartmentId = false,
        public readonly bool $hasEmployeeId = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            inventoryCode: isset($data['codigo_inventario']) ? (string) $data['codigo_inventario'] : (isset($data['id']) ? (string) $data['id'] : null),
            serialNumber: isset($data['numero_serie']) ? (string) $data['numero_serie'] : (isset($data['serialNumber']) ? (string) $data['serialNumber'] : null),
            type: isset($data['tipo']) ? (string) $data['tipo'] : (isset($data['type']) ? (string) $data['type'] : null),
            brand: isset($data['marca']) ? (string) $data['marca'] : null,
            model: isset($data['modelo']) ? (string) $data['modelo'] : ($data['nombre'] ?? null),
            status: isset($data['estado']) ? (string) $data['estado'] : (isset($data['status']) ? (string) $data['status'] : null),
            departmentId: array_key_exists('departamento_id', $data) ? ($data['departamento_id'] !== null ? (int) $data['departamento_id'] : null) : null,
            employeeId: array_key_exists('empleado_id', $data) ? ($data['empleado_id'] !== null ? (int) $data['empleado_id'] : null) : null,
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
            hasDepartmentId: array_key_exists('departamento_id', $data),
            hasEmployeeId: array_key_exists('empleado_id', $data),
        );
    }
}
