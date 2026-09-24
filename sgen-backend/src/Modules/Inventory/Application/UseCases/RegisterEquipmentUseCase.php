<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Application\DTOs\RegisterEquipmentDTO;

final readonly class RegisterEquipmentUseCase
{
    public function execute(RegisterEquipmentDTO $dto): int
    {
        return (int) DB::table('equipos')->insertGetId([
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
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
