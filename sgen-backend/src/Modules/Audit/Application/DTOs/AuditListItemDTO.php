<?php

declare(strict_types=1);

namespace Modules\Audit\Application\DTOs;

final readonly class AuditListItemDTO
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $username,
        public string $avatarInitials,
        public string $startFormatted,
        public string $startTime,
        public ?string $endTime,
        public string $duration,
        public string $status,
        public bool $isActive
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'username' => $this->username,
            'avatar_initials' => $this->avatarInitials,
            'start_formatted' => $this->startFormatted,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'duration' => $this->duration,
            'status' => $this->status,
            'is_active' => $this->isActive,
        ];
    }
}
