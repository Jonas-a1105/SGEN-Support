<?php

declare(strict_types=1);

namespace Modules\Audit\Application\UseCases;

use Modules\Audit\Domain\Models\AuditLogEntry;
use Modules\Audit\Domain\Ports\AuditLogRepositoryInterface;

final class LogActionUseCase
{
    public function __construct(
        private AuditLogRepositoryInterface $repository
    ) {}

    public function execute(
        int $userId,
        string $username,
        string $action,
        string $entityType,
        int $entityId,
        ?array $oldData = null,
        ?array $newData = null,
        string $ipAddress = '127.0.0.1',
        ?string $machineName = null
    ): int {
        $logEntry = AuditLogEntry::create(
            $userId,
            $username,
            $action,
            $entityType,
            $entityId,
            $oldData,
            $newData,
            $ipAddress,
            $machineName
        );

        return $this->repository->save($logEntry);
    }
}
