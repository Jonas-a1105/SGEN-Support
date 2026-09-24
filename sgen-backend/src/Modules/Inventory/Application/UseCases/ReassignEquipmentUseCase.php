<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Application\DTOs\ReassignEquipmentDTO;

final readonly class ReassignEquipmentUseCase
{
    public function execute(ReassignEquipmentDTO $dto): void
    {
        $update = [
            'departamento_id' => $dto->departamentoId,
            'empleado_id' => $dto->empleadoId,
            'updated_at' => now(),
        ];

        if ($dto->ubicacionFisica !== null) {
            $update['ubicacion_fisica'] = $dto->ubicacionFisica;
        }

        DB::table('equipos')->where('id', $dto->equipoId)->update($update);
    }
}
