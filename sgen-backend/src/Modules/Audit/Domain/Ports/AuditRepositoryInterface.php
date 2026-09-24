<?php

declare(strict_types=1);

namespace Modules\Audit\Domain\Ports;

interface AuditRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<array<string, mixed>>
     */
    public function listSessions(array $filters = []): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findSessionById(int $id): ?array;

    /**
     * @return array{active_sessions: int, avg_duration: string, total_logs: int}
     */
    public function getKpis(): array;
}
