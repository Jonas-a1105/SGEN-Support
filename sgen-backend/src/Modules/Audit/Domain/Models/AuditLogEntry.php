<?php

declare(strict_types=1);

namespace Modules\Audit\Domain\Models;

final class AuditLogEntry
{
    public function __construct(
        private ?int $id,
        private int $userId,
        private string $username,
        private string $action,
        private string $entityType,
        private int $entityId,
        private ?array $oldData,
        private ?array $newData,
        private string $ipAddress,
        private ?string $machineName,
        private ?\DateTimeImmutable $createdAt = null
    ) {}

    public static function create(
        int $userId,
        string $username,
        string $action,
        string $entityType,
        int $entityId,
        ?array $oldData = null,
        ?array $newData = null,
        string $ipAddress = '127.0.0.1',
        ?string $machineName = null
    ): self {
        return new self(
            id: null,
            userId: $userId,
            username: $username,
            action: $action,
            entityType: $entityType,
            entityId: $entityId,
            oldData: $oldData,
            newData: $newData,
            ipAddress: $ipAddress,
            machineName: $machineName,
            createdAt: new \DateTimeImmutable()
        );
    }

    public function id(): ?int { return $this->id; }
    public function userId(): int { return $this->userId; }
    public function username(): string { return $this->username; }
    public function action(): string { return $this->action; }
    public function entityType(): string { return $this->entityType; }
    public function entityId(): int { return $this->entityId; }
    public function oldData(): ?array { return $this->oldData; }
    public function newData(): ?array { return $this->newData; }
    public function ipAddress(): string { return $this->ipAddress; }
    public function machineName(): ?string { return $this->machineName; }
    public function createdAt(): ?\DateTimeImmutable { return $this->createdAt; }
}
