<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\DTOs;

final readonly class CreateProductDTO
{
    public function __construct(
        public string $sku,
        public string $name,
        public string $category,
        public int $initialStock,
        public int $minimumStock,
        public float $purchasePrice,
        public ?string $description = null,
        public ?string $brand = null,
        public ?string $model = null,
        public ?string $unitOfMeasure = null,
        public ?string $location = null
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sku: (string) $data['sku'],
            name: (string) $data['name'],
            category: (string) $data['category'],
            initialStock: (int) ($data['initial_stock'] ?? 0),
            minimumStock: (int) ($data['minimum_stock'] ?? 5),
            purchasePrice: (float) ($data['purchase_price'] ?? 0.0),
            description: isset($data['description']) ? (string) $data['description'] : null,
            brand: isset($data['brand']) ? (string) $data['brand'] : null,
            model: isset($data['model']) ? (string) $data['model'] : null,
            unitOfMeasure: isset($data['unit_of_measure']) ? (string) $data['unit_of_measure'] : null,
            location: isset($data['location']) ? (string) $data['location'] : null
        );
    }
}
