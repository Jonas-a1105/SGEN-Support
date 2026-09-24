<?php

declare(strict_types=1);

namespace Modules\Department\Application\UseCases;

use Modules\Department\Domain\Ports\DepartmentRepositoryInterface;

final class AssignEquipmentToDepartmentUseCase
{
    public function __construct(
        private readonly DepartmentRepositoryInterface $repository
    ) {
    }

    public function execute(int $departmentId, int $equipmentId): void
    {
        $this->repository->assignEquipment($departmentId, $equipmentId);
    }
}
