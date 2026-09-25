<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;

/**
 * Registra una pieza usada en una orden de trabajo de forma ATÓMICA:
 * transacción + lockForUpdate sobre inventario_items, descuenta stock real,
 * deja fila espejo en inventario_movimientos y en mantenimiento_materiales.
 */
final class AddMaintenanceMaterialUseCase
{
    public function __construct(
        private readonly MaintenanceRepositoryInterface $repository
    ) {}

    public function execute(int $mantenimientoId, int $itemId, int $cantidad, int $userId): bool
    {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a cero.');
        }

        return DB::transaction(function () use ($mantenimientoId, $itemId, $cantidad, $userId): bool {
            $mantenimiento = DB::table('mantenimientos')->where('id', $mantenimientoId)->first();

            if ($mantenimiento === null) {
                throw new \RuntimeException("La orden de trabajo #{$mantenimientoId} no existe.");
            }

            if (in_array($mantenimiento->estado, ['completado', 'cancelado'], true)) {
                throw new \RuntimeException('No se pueden agregar materiales a una orden completada o cancelada.');
            }

            $item = DB::table('inventario_items')
                ->where('id', $itemId)
                ->lockForUpdate()
                ->first();

            if ($item === null) {
                throw new \RuntimeException("El ítem #{$itemId} no existe en inventario.");
            }

            if ((int) $item->stock_actual < $cantidad) {
                throw InsufficientStockException::forProduct(
                    $item->codigo,
                    (int) $item->stock_actual,
                    $cantidad
                );
            }

            DB::table('inventario_items')
                ->where('id', $itemId)
                ->update([
                    'stock_actual' => DB::raw("stock_actual - {$cantidad}"),
                    'updated_at' => now(),
                ]);

            DB::table('inventario_movimientos')->insert([
                'item_id' => $itemId,
                'usuario_id' => $userId,
                'tipo_movimiento' => 'CONSUMO',
                'cantidad' => $cantidad,
                'motivo' => "Consumo en Mantenimiento #{$mantenimientoId}",
                'referencia_id' => $mantenimientoId,
                'fecha' => now(),
            ]);

            return $this->repository->addMaterial($mantenimientoId, $itemId, $cantidad, $userId);
        });
    }
}
