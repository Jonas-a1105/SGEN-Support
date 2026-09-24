<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\UseCases;

use InvalidArgumentException;
use Modules\Inventory\Application\DTOs\CreateProductDTO;
use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;
use Modules\Inventory\Domain\ValueObjects\Money;
use Modules\Inventory\Domain\ValueObjects\Quantity;
use Modules\Inventory\Domain\ValueObjects\Sku;

final readonly class CreateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function execute(CreateProductDTO $dto): Product
    {
        $existing = $this->repository->findBySku($dto->sku);
        if ($existing !== null) {
            throw new InvalidArgumentException(
                sprintf('Ya existe un producto registrado con el SKU [%s].', $dto->sku)
            );
        }

        $product = Product::create(
            sku: Sku::fromString($dto->sku),
            name: $dto->name,
            category: $dto->category,
            initialStock: Quantity::fromInteger($dto->initialStock),
            minimumStock: Quantity::fromInteger($dto->minimumStock),
            purchasePrice: Money::fromFloat($dto->purchasePrice),
            description: $dto->description,
            brand: $dto->brand,
            model: $dto->model,
            unitOfMeasure: $dto->unitOfMeasure,
            location: $dto->location
        );

        return $this->repository->save($product);
    }
}
