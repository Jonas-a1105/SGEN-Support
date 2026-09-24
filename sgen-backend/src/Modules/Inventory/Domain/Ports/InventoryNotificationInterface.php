<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Ports;

use Modules\Inventory\Domain\Models\Product;

interface InventoryNotificationInterface
{
    public function notifyLowStock(Product $product): void;
}
