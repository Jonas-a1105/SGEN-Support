<?php

declare(strict_types=1);

namespace Modules\Category\Application\UseCases;

use Modules\Category\Domain\Exceptions\CategoryNotFoundException;
use Modules\Category\Domain\Ports\CategoryRepositoryInterface;

final readonly class DeleteCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function execute(int $id): void
    {
        $category = $this->repository->findById($id);
        if ($category === null) {
            throw CategoryNotFoundException::withId($id);
        }

        $this->repository->delete($id);
    }
}
