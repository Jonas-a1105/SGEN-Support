<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\DTOs;

/**
 * Alta rápida de equipo (registro desde inventario/escaneo).
 * Reutiliza los campos del CreateEquipmentDTO pero con validación
 * de unicidad de código patrimonial y serial.
 */
final class RegisterEquipmentDTO
{
    public function __construct(
        public readonly string $codigoInventario,
        public readonly string $tipo,
        public readonly string $marca,
        public readonly string $modelo,
        public readonly string $estado,
        public readonly ?string $numeroSerie = null,
        public readonly ?string $procesador = null,
        public readonly ?string $memoriaRam = null,
        public readonly ?string $almacenamiento = null,
        public readonly ?string $sistemaOperativo = null,
        public readonly ?string $direccionIp = null,
        public readonly ?int $departamentoId = null,
        public readonly ?int $empleadoId = null,
        public readonly ?string $ubicacionFisica = null,
        public readonly ?float $valorCompra = null,
        public readonly ?string $proveedor = null,
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
            estado: (string) ($data['estado'] ?? 'disponible'),
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
            proveedor: ! empty($data['proveedor']) ? (string) $data['proveedor'] : null,
        );
    }
}
