<?php

declare(strict_types=1);

namespace Modules\Audit\Application\DTOs;

final readonly class AuditKpisDTO
{
    public function __construct(
        public int $activeSessions,
        public string $avgDuration,
        public int $totalLogs
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'active_sessions' => $this->activeSessions,
            'avg_duration' => $this->avgDuration,
            'total_logs' => $this->totalLogs,
        ];
    }
}
