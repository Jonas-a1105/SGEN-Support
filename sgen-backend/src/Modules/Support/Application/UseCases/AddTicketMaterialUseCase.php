<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

/**
 * Registra el consumo de un material/repuesto en un ticket de forma ATÓMICA:
 * dentro de una transacción con bloqueo de fila, descuenta el stock real
 * del ítem y deja la fila espejo en inventario_consumos.
 */
final class AddTicketMaterialUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $supportRepository
    ) {
    }

    public function execute(int $ticketId, int $itemId, int $quantity, int $userId): bool
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a cero.');
        }

        return DB::transaction(function () use ($ticketId, $itemId, $quantity, $userId): bool {
            $item = DB::table('inventario_items')
                ->where('id', $itemId)
                ->lockForUpdate()
                ->first();

            if ($item === null) {
                throw new \RuntimeException("El ítem #{$itemId} no existe en inventario.");
            }

            if ((int) $item->stock_actual < $quantity) {
                throw InsufficientStockException::forProduct(
                    $item->codigo,
                    (int) $item->stock_actual,
                    $quantity
                );
            }

            DB::table('inventario_items')
                ->where('id', $itemId)
                ->update([
                    'stock_actual' => DB::raw("stock_actual - {$quantity}"),
                    'updated_at' => now(),
                ]);

            DB::table('inventario_movimientos')->insert([
                'item_id' => $itemId,
                'usuario_id' => $userId,
                'tipo_movimiento' => 'CONSUMO',
                'cantidad' => $quantity,
                'motivo' => "Consumo en Ticket #{$ticketId}",
                'referencia_id' => $ticketId,
                'fecha' => now(),
            ]);

            return $this->supportRepository->addMaterial($ticketId, $itemId, $quantity, $userId);
        });
    }
}
