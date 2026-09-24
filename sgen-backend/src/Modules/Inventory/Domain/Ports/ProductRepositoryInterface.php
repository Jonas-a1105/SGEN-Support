<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Ports;

use Modules\Inventory\Domain\Models\Product;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    public function findBySku(string $sku): ?Product;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<Product>, total: int, per_page: int, current_page: int, last_page: int}
     */
    public function paginate(array $filters = [], int $perPage = 15, int $page = 1): array;

    public function save(Product $product): Product;

    /**
     * @return array{total_items: int, total_units: int, low_stock_count: int, total_valuation: float}
     */
    public function getKpis(): array;
}
