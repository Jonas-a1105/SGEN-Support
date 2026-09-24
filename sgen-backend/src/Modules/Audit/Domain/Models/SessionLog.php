<?php

declare(strict_types=1);

namespace Modules\Audit\Domain\Models;

use DateTimeImmutable;

final class SessionLog
{
    public function __construct(
        private int $id,
        private int $userId,
        private string $username,
        private DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $endedAt = null
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function startedAt(): DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function endedAt(): ?DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function isActive(): bool
    {
        return $this->endedAt === null;
    }

    public function durationFormatted(): string
    {
        if ($this->isActive()) {
            return 'En curso';
        }

        $diff = $this->startedAt->diff($this->endedAt);
        $hours = ($diff->days * 24) + $diff->h;
        $minutes = $diff->i;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes} min";
    }
}
