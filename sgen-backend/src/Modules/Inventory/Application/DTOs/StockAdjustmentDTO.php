<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\DTOs;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Domain\Enums\MovementType;

final readonly class StockAdjustmentDTO
{
    public function __construct(
        public int $productId,
        public int $userId,
        public MovementType $type,
        public int $quantity,
        public string $reason
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) $data['product_id'],
            userId: (int) ($data['user_id'] ?? (function_exists('auth') ? auth()->id() : null) ?? (class_exists(DB::class) ? DB::table('usuarios')->orderBy('id')->value('id') : null) ?? 1),
            type: MovementType::from((string) $data['type']),
            quantity: (int) $data['quantity'],
            reason: (string) ($data['reason'] ?? 'Ajuste de inventario manual')
        );
    }
}
