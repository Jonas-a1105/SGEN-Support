<?php

declare(strict_types=1);

namespace Modules\Employee\Domain\Ports;

use Modules\Employee\Application\DTOs\CreateEmployeeDTO;
use Modules\Employee\Application\DTOs\EmployeeDetailDTO;
use Modules\Employee\Application\DTOs\EmployeeKpisDTO;
use Modules\Employee\Application\DTOs\EmployeeListItemDTO;
use Modules\Employee\Application\DTOs\UpdateEmployeeDTO;

interface EmployeeRepositoryInterface
{
    public function getKpis(): EmployeeKpisDTO;

    /**
     * @param array<string, mixed> $filters
     * @return EmployeeListItemDTO[]
     */
    public function list(array $filters = []): array;

    public function findById(int $id): ?EmployeeDetailDTO;

    public function getCompleteDetail(int $id): ?EmployeeDetailDTO;

    public function create(CreateEmployeeDTO $dto): int;

    public function update(int $id, UpdateEmployeeDTO $dto): void;

    public function delete(int $id): void;

    /**
     * @return array{departments: array<int, array{id: int, nombre: string}>, users: array<int, array{id: int, username: string, rol: string}>}
     */
    public function getFormOptions(): array;
}
