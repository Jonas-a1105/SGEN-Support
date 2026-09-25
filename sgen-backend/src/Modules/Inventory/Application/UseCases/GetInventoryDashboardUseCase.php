<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\UseCases;

use Modules\Inventory\Application\Mappers\ProductDetailMapper;
use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;

final readonly class GetInventoryDashboardUseCase
{
    public function __construct(
        private ListProductsUseCase $listProductsUseCase,
        private GetInventoryKpisUseCase $getInventoryKpisUseCase,
        private ProductRepositoryInterface $productRepository
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $paginated = $this->listProductsUseCase->execute($filters, perPage: $perPage, page: $page);
        $lookups = $this->productRepository->getInventoryDashboardLookups();

        return [
            'products' => array_map(fn (Product $p): array => ProductDetailMapper::toArray($p), $paginated['items']),
            'pagination' => [
                'total' => $paginated['total'],
                'per_page' => $paginated['per_page'],
                'current_page' => $paginated['current_page'],
                'last_page' => $paginated['last_page'],
            ],
            'kpis' => $this->getInventoryKpisUseCase->execute(),
            'filters' => $filters,
            'equipos' => $lookups['equipos'],
            'departamentos' => $lookups['departamentos'],
            'empleados' => $lookups['empleados'],
            'movimientos' => $this->productRepository->getRecentMovements(50),
            'ubicaciones' => $this->productRepository->getLocations(),
        ];
    }
}
