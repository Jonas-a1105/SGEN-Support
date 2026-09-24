<?php

declare(strict_types=1);

namespace Modules\Employee\Application\UseCases;

use Modules\Employee\Domain\Ports\EmployeeRepositoryInterface;

final class DeleteEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $repository
    ) {
    }

    public function execute(int $id): void
    {
        $this->repository->delete($id);
    }
}
