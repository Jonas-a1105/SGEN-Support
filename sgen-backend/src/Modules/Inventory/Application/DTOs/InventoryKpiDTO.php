<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\DTOs;

final readonly class InventoryKpiDTO
{
    public function __construct(
        public int $totalItems,
        public int $totalUnits,
        public int $lowStockCount,
        public float $totalValuation
    ) {}

    /**
     * @param  array{total_items: int, total_units: int, low_stock_count: int, total_valuation: float}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            totalItems: (int) $data['total_items'],
            totalUnits: (int) $data['total_units'],
            lowStockCount: (int) $data['low_stock_count'],
            totalValuation: (float) $data['total_valuation']
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_items' => $this->totalItems,
            'total_units' => $this->totalUnits,
            'low_stock_count' => $this->lowStockCount,
            'total_valuation' => $this->totalValuation,
        ];
    }
}
