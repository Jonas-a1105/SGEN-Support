<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\UseCases;

use Modules\Maintenance\Application\DTOs\MaintenanceKpisDTO;
use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;

final class GetMaintenanceKpisUseCase
{
    public function __construct(
        private MaintenanceRepositoryInterface $repository
    ) {}

    public function execute(?int $currentUserId = null): MaintenanceKpisDTO
    {
        return $this->repository->getKpis($currentUserId);
    }
}
