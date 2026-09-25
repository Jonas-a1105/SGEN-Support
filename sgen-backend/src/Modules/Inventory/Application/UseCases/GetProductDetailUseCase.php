<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\UseCases;

use Modules\Inventory\Application\Mappers\ProductDetailMapper;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;

final readonly class GetProductDetailUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    /**
     * @return array{product: array<string, mixed>, movements: array<int, object>}|null
     */
    public function execute(int $id): ?array
    {
        $product = $this->repository->findById($id);

        if ($product === null) {
            return null;
        }

        $movements = $this->repository->getMovementsByProductId($id);

        return [
            'product' => ProductDetailMapper::toArray($product),
            'movements' => $movements,
        ];
    }
}
