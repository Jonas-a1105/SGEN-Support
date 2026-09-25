<?php

declare(strict_types=1);

namespace Modules\Notification\Domain\Models;

use Modules\Notification\Domain\Enums\NotificationType;

final class Notification
{
    public function __construct(
        private ?int $id,
        private int $userId,
        private NotificationType $type,
        private string $title,
        private string $message,
        private ?string $link,
        private bool $isRead = false,
        private ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $readAt = null
    ) {}

    public static function create(
        int $userId,
        NotificationType $type,
        string $title,
        string $message,
        ?string $link = null
    ): self {
        return new self(
            id: null,
            userId: $userId,
            type: $type,
            title: $title,
            message: $message,
            link: $link,
            isRead: false,
            createdAt: new \DateTimeImmutable()
        );
    }

    public function markAsRead(): void
    {
        $this->isRead = true;
        $this->readAt = new \DateTimeImmutable();
    }

    public function id(): ?int { return $this->id; }
    public function userId(): int { return $this->userId; }
    public function type(): NotificationType { return $this->type; }
    public function title(): string { return $this->title; }
    public function message(): string { return $this->message; }
    public function link(): ?string { return $this->link; }
    public function isRead(): bool { return $this->isRead; }
    public function createdAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function readAt(): ?\DateTimeImmutable { return $this->readAt; }
}
