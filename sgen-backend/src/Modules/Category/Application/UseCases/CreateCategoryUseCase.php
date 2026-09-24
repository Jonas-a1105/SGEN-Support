<?php

declare(strict_types=1);

namespace Modules\Category\Application\UseCases;

use Modules\Category\Application\DTOs\CreateCategoryDTO;
use Modules\Category\Domain\Models\Category;
use Modules\Category\Domain\Ports\CategoryRepositoryInterface;

final readonly class CreateCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(CreateCategoryDTO $dto): int
    {
        $category = Category::create(
            name: $dto->nombre,
            description: $dto->descripcion,
            icon: $dto->icono,
            color: $dto->color,
            active: $dto->activo
        );

        return $this->repository->save($category);
    }
}
