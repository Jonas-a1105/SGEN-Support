<?php

declare(strict_types=1);

namespace Modules\Reports\Domain\Ports;

interface ReportsRepositoryInterface
{
    public function getFormData(): array;

    public function getTicketsData(array $filters): array;

    public function getInventoryData(): array;

    public function getMaintenanceData(): array;

    public function getPerformanceData(): array;
}
