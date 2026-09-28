<?php

declare(strict_types=1);

namespace Modules\Category\Application\UseCases;

use Modules\Category\Application\DTOs\UpdateCategoryDTO;
use Modules\Category\Domain\Exceptions\CategoryNotFoundException;
use Modules\Category\Domain\Ports\CategoryRepositoryInterface;

final readonly class UpdateCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(int $id, UpdateCategoryDTO $dto): void
    {
        $category = $this->repository->findById($id);
        if ($category === null) {
            throw CategoryNotFoundException::withId($id);
        }

        $updated = $category->update(
            name: $dto->nombre,
            description: $dto->descripcion,
            icon: $dto->icono,
            color: $dto->color
        );

        // El estado activo es parte del agregado: se proyecta con las
        // transiciones del dominio. Si el payload no lo trae, se conserva
        // el estado actual (actualización parcial).
        if ($dto->activo !== null) {
            $updated = $dto->activo ? $updated->activate() : $updated->deactivate();
        }

        $this->repository->update($updated);
    }
}
