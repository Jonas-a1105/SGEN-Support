<?php

declare(strict_types=1);

namespace Modules\Employee\Application\UseCases;

use Modules\Employee\Application\DTOs\UpdateEmployeeDTO;
use Modules\Employee\Domain\Ports\EmployeeRepositoryInterface;

final class UpdateEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $repository
    ) {
    }

    public function execute(int $id, UpdateEmployeeDTO $dto): void
    {
        $this->repository->update($id, $dto);
    }
}
