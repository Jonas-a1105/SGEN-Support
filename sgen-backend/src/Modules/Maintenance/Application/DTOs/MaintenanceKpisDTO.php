<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\DTOs;

final readonly class MaintenanceKpisDTO
{
    public function __construct(
        public int $pending,
        public int $inProcess,
        public int $completed,
        public int $upcoming,
        public int $overdue,
        public int $total
    ) {}
}
