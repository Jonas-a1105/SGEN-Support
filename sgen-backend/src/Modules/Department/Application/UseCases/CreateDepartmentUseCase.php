<?php

declare(strict_types=1);

namespace Modules\Department\Application\UseCases;

use Modules\Department\Application\DTOs\CreateDepartmentDTO;
use Modules\Department\Domain\Ports\DepartmentRepositoryInterface;

final class CreateDepartmentUseCase
{
    public function __construct(
        private readonly DepartmentRepositoryInterface $repository
    ) {
    }

    public function execute(CreateDepartmentDTO $dto): int
    {
        return $this->repository->create($dto);
    }
}
