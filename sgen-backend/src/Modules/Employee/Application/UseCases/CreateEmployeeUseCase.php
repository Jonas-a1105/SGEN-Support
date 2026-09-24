<?php

declare(strict_types=1);

namespace Modules\Employee\Application\UseCases;

use Modules\Employee\Application\DTOs\CreateEmployeeDTO;
use Modules\Employee\Domain\Ports\EmployeeRepositoryInterface;

final class CreateEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $repository
    ) {
    }

    public function execute(CreateEmployeeDTO $dto): int
    {
        return $this->repository->create($dto);
    }
}
