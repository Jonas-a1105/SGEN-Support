<?php

declare(strict_types=1);

namespace Modules\Audit\Domain\Ports;

use Modules\Audit\Domain\Models\AuditLogEntry;

interface AuditLogRepositoryInterface
{
    public function save(AuditLogEntry $logEntry): int;
    public function findByEntity(string $entityType, int $entityId): array;
    public function findByUser(int $userId, int $limit = 50): array;
    public function findRecent(int $limit = 50): array;
    public function countAll(): int;
    public function countByUser(int $userId): int;
    public function countByEntity(string $entityType, int $entityId): int;
    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function listActions(array $filters = [], int $limit = 50): array;
}
