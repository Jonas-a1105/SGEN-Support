<?php

declare(strict_types=1);

namespace App\Infrastructure\Inventory\Notifications;

use Illuminate\Support\Facades\Log;
use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\Ports\InventoryNotificationInterface;

final class LogInventoryNotificationService implements InventoryNotificationInterface
{
    public function notifyLowStock(Product $product): void
    {
        Log::warning(
            sprintf(
                'Alerta de inventario crítico: El producto [%s - %s] tiene stock actual de %d (Mínimo requerido: %d).',
                $product->sku()->value(),
                $product->name(),
                $product->currentStock()->value(),
                $product->minimumStock()->value()
            ),
            [
                'product_id' => $product->id(),
                'sku' => $product->sku()->value(),
                'stock' => $product->currentStock()->value(),
            ]
        );
    }
}
