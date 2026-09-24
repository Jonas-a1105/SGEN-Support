<?php

declare(strict_types=1);

namespace Modules\Department\Application\UseCases;

use Modules\Department\Domain\Ports\DepartmentRepositoryInterface;

final class RemoveEmployeeFromDepartmentUseCase
{
    public function __construct(
        private readonly DepartmentRepositoryInterface $repository
    ) {
    }

    public function execute(int $employeeId): void
    {
        $this->repository->removeEmployee($employeeId);
    }
}
