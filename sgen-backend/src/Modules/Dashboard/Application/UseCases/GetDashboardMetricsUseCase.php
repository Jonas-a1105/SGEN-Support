<?php

declare(strict_types=1);

namespace Modules\Dashboard\Application\UseCases;

use Modules\Dashboard\Application\DTOs\DashboardMetricsDTO;
use Modules\Dashboard\Domain\Ports\DashboardRepositoryInterface;

final class GetDashboardMetricsUseCase
{
    public function __construct(
        private readonly DashboardRepositoryInterface $repository
    ) {
    }

    public function execute(int $year = 2025): DashboardMetricsDTO
    {
        $kpis = $this->repository->getKpiMetrics();
        $ticketVolume = $this->repository->getTicketVolumeByYear($year);
        $topCategory = $this->repository->getTopCategory();
        $inventoryHealth = $this->repository->getInventoryHealth();
        $technicians = $this->repository->getTechnicianPerformance();
        $recentActivity = $this->repository->getRecentActivity(5);
        $ticketsByStatus = $this->repository->getTicketsByStatus();

        return new DashboardMetricsDTO(
            kpis: $kpis,
            ticketVolume: $ticketVolume,
            topCategory: $topCategory,
            inventoryHealth: $inventoryHealth,
            technicians: $technicians,
            recentActivity: $recentActivity,
            ticketsByStatus: $ticketsByStatus
        );
    }
}
