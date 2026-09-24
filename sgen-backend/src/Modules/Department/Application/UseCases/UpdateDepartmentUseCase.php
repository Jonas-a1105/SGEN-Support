<?php

declare(strict_types=1);

namespace Modules\Department\Application\UseCases;

use Modules\Department\Application\DTOs\UpdateDepartmentDTO;
use Modules\Department\Domain\Ports\DepartmentRepositoryInterface;

final class UpdateDepartmentUseCase
{
    public function __construct(
        private readonly DepartmentRepositoryInterface $repository
    ) {
    }

    public function execute(int $id, UpdateDepartmentDTO $dto): void
    {
        $this->repository->update($id, $dto);
    }
}
