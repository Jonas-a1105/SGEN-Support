<?php

declare(strict_types=1);

namespace Modules\Employee\Application\DTOs;

final class EmployeeKpisDTO
{
    public function __construct(
        public readonly int $totalEmp,
        public readonly int $activeUsers,
        public readonly int $noUsers,
    ) {
    }

    public function toArray(): array
    {
        return [
            'totalEmp' => $this->totalEmp,
            'activeUsers' => $this->activeUsers,
            'noUsers' => $this->noUsers,
        ];
    }
}
