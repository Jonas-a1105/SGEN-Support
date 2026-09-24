<?php

declare(strict_types=1);

namespace Modules\User\Application\DTOs;

final readonly class UserKpisDTO
{
    public function __construct(
        public int $totalUsers,
        public int $adminsCount,
        public int $techsCount
    ) {}

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'total_users' => $this->totalUsers,
            'admins_count' => $this->adminsCount,
            'techs_count' => $this->techsCount,
        ];
    }
}
