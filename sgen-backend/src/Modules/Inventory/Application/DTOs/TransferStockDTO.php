<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\DTOs;

final readonly class TransferStockDTO
{
    public function __construct(
        public int $itemId,
        public ?int $origenId,
        public int $destinoId,
        public int $cantidad,
        public ?string $motivo = null
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            itemId: (int) $data['item_id'],
            origenId: ! empty($data['origen_id']) ? (int) $data['origen_id'] : null,
            destinoId: (int) $data['destino_id'],
            cantidad: (int) $data['cantidad'],
            motivo: ! empty($data['motivo']) ? (string) $data['motivo'] : null
        );
    }
}
