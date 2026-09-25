<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Notifications;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\Ports\InventoryNotificationInterface;
use Modules\Notification\Application\UseCases\CreateNotificationUseCase;
use Modules\Notification\Domain\Enums\NotificationType;

final class LogInventoryNotificationService implements InventoryNotificationInterface
{
    public function __construct(
        private readonly CreateNotificationUseCase $createNotificationUseCase
    ) {}

    public function notifyLowStock(Product $product): void
    {
        $message = sprintf(
            'El producto [%s - %s] tiene stock crítico de %d (Mínimo requerido: %d).',
            $product->sku()->value(),
            $product->name(),
            $product->currentStock()->value(),
            $product->minimumStock()->value()
        );

        Log::warning("Alerta de inventario: {$message}", [
            'product_id' => $product->id(),
            'sku' => $product->sku()->value(),
            'stock' => $product->currentStock()->value(),
        ]);

        if ($this->createNotificationUseCase && $product->id()) {
            try {
                $targetUserIds = DB::table('usuarios')
                    ->whereIn('rol', ['admin', 'tecnico'])
                    ->pluck('id');

                foreach ($targetUserIds as $userId) {
                    $this->createNotificationUseCase->execute(
                        (int) $userId,
                        NotificationType::INVENTARIO_BAJO_STOCK,
                        'Alerta de Stock Crítico',
                        $message,
                        "/inventario/{$product->id()}"
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Fallo al despachar notificación de inventario en BD: '.$e->getMessage());
            }
        }
    }
}
