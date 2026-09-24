<?php

declare(strict_types=1);

namespace Modules\Category\Application\DTOs;

final readonly class CategoryKpisDTO
{
    public function __construct(
        public int $totalActivas,
        public string $hardwareStat,
        public string $redStat
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_activas' => $this->totalActivas,
            'hardware_stat' => $this->hardwareStat,
            'red_stat' => $this->redStat,
        ];
    }
}
