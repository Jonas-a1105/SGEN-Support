<?php

declare(strict_types=1);

namespace Modules\Category\Application\UseCases;

use Modules\Category\Application\DTOs\CategoryKpisDTO;
use Modules\Category\Application\Mappers\CategoryListItemMapper;
use Modules\Category\Domain\Ports\CategoryRepositoryInterface;

final readonly class GetCategoryListUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters = []): array
    {
        $rawRows = $this->repository->listWithStats($filters);
        $kpisData = $this->repository->getKpis();

        $categories = array_map(
            fn (object|array $row): array => CategoryListItemMapper::fromRow($row)->toArray(),
            $rawRows
        );

        $kpis = new CategoryKpisDTO(
            totalActivas: $kpisData['total_activas'],
            hardwareStat: $kpisData['hardware_stat'],
            redStat: $kpisData['red_stat']
        );

        return [
            'categories' => $categories,
            'kpis' => $kpis->toArray(),
            'filters' => $filters,
        ];
    }
}
