<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\UseCases;

use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;

final class GetUpcomingMaintenanceUseCase
{
    public function __construct(
        private MaintenanceRepositoryInterface $repository
    ) {}

    public function execute(int $days = 30): array
    {
        return $this->repository->getUpcoming($days);
    }
}
