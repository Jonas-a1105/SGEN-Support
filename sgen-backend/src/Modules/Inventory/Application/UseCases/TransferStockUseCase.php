<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Application\DTOs\TransferStockDTO;

final readonly class TransferStockUseCase
{
    public function execute(TransferStockDTO $dto, int $userId): void
    {
        DB::transaction(function () use ($dto, $userId): void {
            if ($dto->origenId !== null) {
                DB::table('inventario_ubicaciones')
                    ->where('item_id', $dto->itemId)
                    ->where('departamento_id', $dto->origenId)
                    ->decrement('cantidad', $dto->cantidad);
            }

            $dest = DB::table('inventario_ubicaciones')
                ->where('item_id', $dto->itemId)
                ->where('departamento_id', $dto->destinoId)
                ->first();

            if ($dest) {
                DB::table('inventario_ubicaciones')
                    ->where('id', $dest->id)
                    ->increment('cantidad', $dto->cantidad);
            } else {
                DB::table('inventario_ubicaciones')->insert([
                    'item_id' => $dto->itemId,
                    'departamento_id' => $dto->destinoId,
                    'cantidad' => $dto->cantidad,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('inventario_movimientos')->insert([
                'item_id' => $dto->itemId,
                'usuario_id' => $userId,
                'origen_departamento_id' => $dto->origenId,
                'destino_departamento_id' => $dto->destinoId,
                'tipo_movimiento' => 'TRANSFERENCIA',
                'cantidad' => $dto->cantidad,
                'motivo' => $dto->motivo ?? 'Transferencia entre almacenes',
                'fecha' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}
