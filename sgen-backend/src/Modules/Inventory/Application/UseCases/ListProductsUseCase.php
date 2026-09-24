<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\UseCases;

use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;

final readonly class ListProductsUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<Product>, total: int, per_page: int, current_page: int, last_page: int}
     */
    public function execute(array $filters = [], int $perPage = 15, int $page = 1): array
    {
        return $this->repository->paginate($filters, $perPage, $page);
    }
}
