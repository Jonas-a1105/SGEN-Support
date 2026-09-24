<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Inventory;

use InvalidArgumentException;
use Modules\Inventory\Domain\Enums\MovementType;
use Modules\Inventory\Domain\Enums\ProductStatus;
use Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use Modules\Inventory\Domain\Exceptions\InvalidQuantityException;
use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\ValueObjects\Money;
use Modules\Inventory\Domain\ValueObjects\Quantity;
use Modules\Inventory\Domain\ValueObjects\Sku;
use PHPUnit\Framework\TestCase;

final class ProductDomainTest extends TestCase
{
    public function test_can_instantiate_product_with_valid_invariants(): void
    {
        $product = Product::create(
            sku: Sku::fromString('MEM-DDR4-16GB'),
            name: 'Memoria RAM Kingston 16GB',
            category: 'Memorias',
            initialStock: Quantity::fromInteger(20),
            minimumStock: Quantity::fromInteger(5),
            purchasePrice: Money::fromFloat(45.50),
            description: 'Módulo DDR4 3200MHz',
            brand: 'Kingston',
            model: 'Fury Beast'
        );

        $this->assertSame('MEM-DDR4-16GB', $product->sku()->value());
        $this->assertSame('Memoria RAM Kingston 16GB', $product->name());
        $this->assertSame(20, $product->currentStock()->value());
        $this->assertSame(5, $product->minimumStock()->value());
        $this->assertSame(45.50, $product->purchasePrice()->amount());
        $this->assertSame(ProductStatus::ACTIVE, $product->status());
        $this->assertFalse($product->isLowStock());
    }

    public function test_stock_reduction_creates_recorded_movement(): void
    {
        $product = Product::create(
            sku: Sku::fromString('DISC-SSD-1TB'),
            name: 'SSD Samsung 1TB',
            category: 'Almacenamiento',
            initialStock: Quantity::fromInteger(10),
            minimumStock: Quantity::fromInteger(3),
            purchasePrice: Money::fromFloat(80.0)
        );

        $movement = $product->adjustStock(
            delta: Quantity::fromInteger(4),
            type: MovementType::SALIDA,
            userId: 1,
            reason: 'Despacho a soporte técnico'
        );

        $this->assertSame(6, $product->currentStock()->value());
        $this->assertSame(MovementType::SALIDA, $movement->type());
        $this->assertSame(4, $movement->quantity()->value());
        $this->assertCount(1, $product->pullRecordedMovements());
    }

    public function test_insufficient_stock_throws_domain_exception(): void
    {
        $product = Product::create(
            sku: Sku::fromString('PROC-I7-12700'),
            name: 'Intel Core i7',
            category: 'Procesadores',
            initialStock: Quantity::fromInteger(3),
            minimumStock: Quantity::fromInteger(2),
            purchasePrice: Money::fromFloat(320.0)
        );

        $this->expectException(InsufficientStockException::class);

        $product->adjustStock(
            delta: Quantity::fromInteger(5),
            type: MovementType::SALIDA,
            userId: 1,
            reason: 'Consumo no permitido'
        );
    }

    public function test_negative_quantity_throws_invalid_quantity_exception(): void
    {
        $this->expectException(InvalidQuantityException::class);

        Quantity::fromInteger(-10);
    }

    public function test_invalid_sku_throws_invalid_argument_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Sku::fromString('A');
    }

    public function test_detects_low_stock_correctly(): void
    {
        $product = Product::create(
            sku: Sku::fromString('CBL-ETH-CAT6'),
            name: 'Bobina Cable Red',
            category: 'Cables',
            initialStock: Quantity::fromInteger(4),
            minimumStock: Quantity::fromInteger(5),
            purchasePrice: Money::fromFloat(65.0)
        );

        $this->assertTrue($product->isLowStock());
    }

    public function test_stock_addition_increases_stock(): void
    {
        $product = Product::create(
            sku: Sku::fromString('FUENTE-650W'),
            name: 'Fuente EVGA 650W',
            category: 'Energía',
            initialStock: Quantity::fromInteger(5),
            minimumStock: Quantity::fromInteger(2),
            purchasePrice: Money::fromFloat(75.0)
        );

        $product->adjustStock(
            delta: Quantity::fromInteger(8),
            type: MovementType::ENTRADA,
            userId: 1,
            reason: 'Llegada de proveedor'
        );

        $this->assertSame(13, $product->currentStock()->value());
    }
}
