<?php

declare(strict_types=1);

namespace Modules\Department\Domain\Ports;

use Modules\Department\Application\DTOs\CreateDepartmentDTO;
use Modules\Department\Application\DTOs\DepartmentDetailDTO;
use Modules\Department\Application\DTOs\DepartmentListItemDTO;
use Modules\Department\Application\DTOs\UpdateDepartmentDTO;

interface DepartmentRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return DepartmentListItemDTO[]
     */
    public function list(array $filters = []): array;

    public function findById(int $id): ?DepartmentDetailDTO;

    public function getCompleteDetail(int $id): ?DepartmentDetailDTO;

    public function create(CreateDepartmentDTO $dto): int;

    public function update(int $id, UpdateDepartmentDTO $dto): void;

    public function delete(int $id): void;

    public function assignEmployee(int $departmentId, int $employeeId): void;

    public function removeEmployee(int $employeeId): void;

    public function assignEquipment(int $departmentId, int $equipmentId): void;

    public function removeEquipment(int $equipmentId): void;

    /**
     * @return array<int, array{id: int, name: string, role: string, init: string}>
     */
    public function getCandidatesForLeadership(): array;
}
