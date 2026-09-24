<?php

declare(strict_types=1);

namespace Modules\Support\Application\DTOs;

final class SupportKpisDTO
{
    public function __construct(
        public readonly int $criticalPending = 0,
        public readonly int $generalQueue = 0,
        public readonly int $inProcess = 0,
        public readonly int $myAssignments = 0,
        public readonly int $totalTickets = 0,
        public readonly int $resolvedTickets = 0
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'critical_pending' => $this->criticalPending,
            'general_queue' => $this->generalQueue,
            'in_process' => $this->inProcess,
            'my_assignments' => $this->myAssignments,
            'total_tickets' => $this->totalTickets,
            'resolved_tickets' => $this->resolvedTickets,
        ];
    }
}
