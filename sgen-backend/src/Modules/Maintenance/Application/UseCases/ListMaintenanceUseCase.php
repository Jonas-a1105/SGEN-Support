<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\UseCases;

use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;

final class ListMaintenanceUseCase
{
    public function __construct(
        private MaintenanceRepositoryInterface $repository
    ) {}

    public function execute(array $filters = []): array
    {
        return $this->repository->listMaintenance($filters);
    }
}
