<?php

declare(strict_types=1);

namespace Modules\Employee\Application\UseCases;

use Modules\Employee\Application\DTOs\EmployeeDetailDTO;
use Modules\Employee\Domain\Ports\EmployeeRepositoryInterface;

final class GetEmployeeDetailUseCase
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $repository
    ) {
    }

    public function execute(int $id): ?EmployeeDetailDTO
    {
        return $this->repository->getCompleteDetail($id);
    }
}
