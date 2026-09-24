<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\UseCases;

use Modules\Inventory\Application\DTOs\StockAdjustmentDTO;
use Modules\Inventory\Domain\Exceptions\ProductNotFoundException;
use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\Ports\InventoryNotificationInterface;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;
use Modules\Inventory\Domain\ValueObjects\Quantity;

final readonly class AdjustStockUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
        private InventoryNotificationInterface $notifier
    ) {}

    public function execute(StockAdjustmentDTO $dto): Product
    {
        $product = $this->repository->findById($dto->productId);

        if ($product === null) {
            throw ProductNotFoundException::withId($dto->productId);
        }

        $product->adjustStock(
            delta: Quantity::fromInteger($dto->quantity),
            type: $dto->type,
            userId: $dto->userId,
            reason: $dto->reason
        );

        $savedProduct = $this->repository->save($product);

        if ($savedProduct->isLowStock()) {
            $this->notifier->notifyLowStock($savedProduct);
        }

        return $savedProduct;
    }
}
