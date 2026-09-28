<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\UseCases;

use Modules\Inventory\Application\DTOs\StockAdjustmentDTO;
use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\Ports\InventoryNotificationInterface;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;

final readonly class AdjustStockUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
        private InventoryNotificationInterface $notifier
    ) {}

    public function execute(StockAdjustmentDTO $dto): Product
    {
        $savedProduct = $this->repository->adjustStock(
            productId: $dto->productId,
            type: $dto->type,
            quantity: $dto->quantity,
            userId: $dto->userId,
            reason: $dto->reason
        );

        if ($savedProduct->isLowStock()) {
            $this->notifier->notifyLowStock($savedProduct);
        }

        return $savedProduct;
    }
}
