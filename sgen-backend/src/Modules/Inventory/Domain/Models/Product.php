<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Models;

use Modules\Inventory\Domain\Enums\MovementType;
use Modules\Inventory\Domain\Enums\ProductStatus;
use Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use Modules\Inventory\Domain\ValueObjects\Money;
use Modules\Inventory\Domain\ValueObjects\Quantity;
use Modules\Inventory\Domain\ValueObjects\Sku;

final class Product
{
    /** @var array<StockMovement> */
    private array $recordedMovements = [];

    public function __construct(
        private ?int $id,
        private Sku $sku,
        private string $name,
        private string $category,
        private ?string $description,
        private ?string $brand,
        private ?string $model,
        private ?string $unitOfMeasure,
        private Quantity $currentStock,
        private Quantity $minimumStock,
        private ?string $location,
        private Money $purchasePrice,
        private ProductStatus $status = ProductStatus::ACTIVE
    ) {}

    public static function create(
        Sku $sku,
        string $name,
        string $category,
        Quantity $initialStock,
        Quantity $minimumStock,
        Money $purchasePrice,
        ?string $description = null,
        ?string $brand = null,
        ?string $model = null,
        ?string $unitOfMeasure = null,
        ?string $location = null
    ): self {
        return new self(
            id: null,
            sku: $sku,
            name: $name,
            category: $category,
            description: $description,
            brand: $brand,
            model: $model,
            unitOfMeasure: $unitOfMeasure,
            currentStock: $initialStock,
            minimumStock: $minimumStock,
            location: $location,
            purchasePrice: $purchasePrice,
            status: ProductStatus::ACTIVE
        );
    }

    public function adjustStock(
        Quantity $delta,
        MovementType $type,
        int $userId,
        string $reason
    ): StockMovement {
        if ($type->isReduction()) {
            if ($this->currentStock->isLessThan($delta)) {
                throw InsufficientStockException::forProduct(
                    $this->sku->value(),
                    $this->currentStock->value(),
                    $delta->value()
                );
            }
            $this->currentStock = $this->currentStock->subtract($delta);
        } elseif ($type->isAddition()) {
            $this->currentStock = $this->currentStock->add($delta);
        } else {
            // Caso AJUSTE arbitrario: delta es el nuevo stock objetivo
            $this->currentStock = $delta;
        }

        $movement = StockMovement::create(
            productId: $this->id ?? 0,
            userId: $userId,
            type: $type,
            quantity: $delta,
            reason: $reason
        );

        $this->recordedMovements[] = $movement;

        return $movement;
    }

    public function isLowStock(): bool
    {
        return $this->currentStock->isLessThanOrEqual($this->minimumStock);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function sku(): Sku
    {
        return $this->sku;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function category(): string
    {
        return $this->category;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function brand(): ?string
    {
        return $this->brand;
    }

    public function model(): ?string
    {
        return $this->model;
    }

    public function unitOfMeasure(): ?string
    {
        return $this->unitOfMeasure;
    }

    public function currentStock(): Quantity
    {
        return $this->currentStock;
    }

    public function minimumStock(): Quantity
    {
        return $this->minimumStock;
    }

    public function location(): ?string
    {
        return $this->location;
    }

    public function purchasePrice(): Money
    {
        return $this->purchasePrice;
    }

    public function status(): ProductStatus
    {
        return $this->status;
    }

    /**
     * @return array<StockMovement>
     */
    public function pullRecordedMovements(): array
    {
        $movements = $this->recordedMovements;
        $this->recordedMovements = [];

        return $movements;
    }
}
