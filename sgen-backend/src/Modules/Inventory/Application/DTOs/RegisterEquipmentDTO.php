<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\DTOs;

final readonly class RegisterEquipmentDTO
{
    public function __construct(
        public string $codigoInventario,
        public string $tipo,
        public string $marca,
        public string $modelo,
        public string $estado,
        public ?string $numeroSerie = null,
        public ?string $procesador = null,
        public ?string $memoriaRam = null,
        public ?string $almacenamiento = null,
        public ?string $sistemaOperativo = null,
        public ?string $direccionIp = null,
        public ?int $departamentoId = null,
        public ?int $empleadoId = null,
        public ?string $ubicacionFisica = null,
        public ?float $valorCompra = null,
        public ?string $proveedor = null
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            codigoInventario: (string) $data['codigo_inventario'],
            tipo: (string) $data['tipo'],
            marca: (string) $data['marca'],
            modelo: (string) $data['modelo'],
            estado: (string) ($data['estado'] ?? 'Disponible'),
            numeroSerie: ! empty($data['numero_serie']) ? (string) $data['numero_serie'] : null,
            procesador: ! empty($data['procesador']) ? (string) $data['procesador'] : null,
            memoriaRam: ! empty($data['memoria_ram']) ? (string) $data['memoria_ram'] : null,
            almacenamiento: ! empty($data['almacenamiento']) ? (string) $data['almacenamiento'] : null,
            sistemaOperativo: ! empty($data['sistema_operativo']) ? (string) $data['sistema_operativo'] : null,
            direccionIp: ! empty($data['direccion_ip']) ? (string) $data['direccion_ip'] : null,
            departamentoId: ! empty($data['departamento_id']) ? (int) $data['departamento_id'] : null,
            empleadoId: ! empty($data['empleado_id']) ? (int) $data['empleado_id'] : null,
            ubicacionFisica: ! empty($data['ubicacion_fisica']) ? (string) $data['ubicacion_fisica'] : null,
            valorCompra: isset($data['valor_compra']) && $data['valor_compra'] !== '' ? (float) $data['valor_compra'] : null,
            proveedor: ! empty($data['proveedor']) ? (string) $data['proveedor'] : null
        );
    }
}
