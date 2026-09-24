<?php

declare(strict_types=1);

namespace Modules\Maintenance\Domain\Ports;

use Modules\Maintenance\Application\DTOs\MaintenanceKpisDTO;
use Modules\Maintenance\Application\DTOs\CreateMaintenanceDTO;
use Modules\Maintenance\Application\DTOs\UpdateMaintenanceDTO;
use Modules\Maintenance\Application\DTOs\MaintenanceDetailDTO;

interface MaintenanceRepositoryInterface
{
    public function getKpis(?int $currentUserId = null): MaintenanceKpisDTO;

    public function listMaintenance(array $filters = []): array;

    public function findById(int $id): ?MaintenanceDetailDTO;

    public function create(CreateMaintenanceDTO $dto, ?int $userId = null): int;

    public function update(int $id, UpdateMaintenanceDTO $dto): bool;

    public function delete(int $id): bool;

    public function deleteBulk(array $ids): int;

    public function complete(int $id, ?string $observations = null, ?float $cost = null): bool;

    public function postpone(int $id, string $newDate): bool;

    public function cancel(int $id, string $reason): bool;

    public function getUpcoming(int $days = 30): array;

    public function getFormOptions(): array;
}
