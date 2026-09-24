<?php

declare(strict_types=1);

namespace Modules\Category\Domain\Ports;

use Modules\Category\Domain\Models\Category;

interface CategoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<array<string, mixed>>
     */
    public function listWithStats(array $filters = []): array;

    public function findById(int $id): ?Category;

    public function save(Category $category): int;

    public function update(Category $category): void;

    public function delete(int $id): void;

    /**
     * @return array{total_activas: int, hardware_stat: string, red_stat: string}
     */
    public function getKpis(): array;
}
