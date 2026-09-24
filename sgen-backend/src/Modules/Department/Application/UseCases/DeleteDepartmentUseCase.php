<?php

declare(strict_types=1);

namespace Modules\Department\Application\UseCases;

use Modules\Department\Domain\Ports\DepartmentRepositoryInterface;

final class DeleteDepartmentUseCase
{
    public function __construct(
        private readonly DepartmentRepositoryInterface $repository
    ) {
    }

    public function execute(int $id): void
    {
        $this->repository->delete($id);
    }
}
