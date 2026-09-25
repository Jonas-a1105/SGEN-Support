<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\UseCases;

use Modules\Equipment\Application\DTOs\CreateEquipmentDTO;
use Modules\Equipment\Application\DTOs\RegisterEquipmentDTO;

/**
 * Registra un equipo nuevo (alta rápida) con unicidad validada
 * de código patrimonial y número de serie.
 */
final class RegisterEquipmentUseCase
{
    public function __construct(
        private readonly CreateEquipmentUseCase $createEquipmentUseCase
    ) {
    }

    public function execute(RegisterEquipmentDTO $dto): int
    {
        return $this->createEquipmentUseCase->execute(CreateEquipmentDTO::fromArray([
            'codigo_inventario' => $dto->codigoInventario,
            'tipo' => $dto->tipo,
            'marca' => $dto->marca,
            'modelo' => $dto->modelo,
            'estado' => $dto->estado,
            'numero_serie' => $dto->numeroSerie,
            'procesador' => $dto->procesador,
            'memoria_ram' => $dto->memoriaRam,
            'almacenamiento' => $dto->almacenamiento,
            'sistema_operativo' => $dto->sistemaOperativo,
            'direccion_ip' => $dto->direccionIp,
            'departamento_id' => $dto->departamentoId,
            'empleado_id' => $dto->empleadoId,
            'ubicacion_fisica' => $dto->ubicacionFisica,
            'valor_compra' => $dto->valorCompra,
            'proveedor' => $dto->proveedor,
        ]));
    }
}
