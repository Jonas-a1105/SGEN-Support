<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Inventory;

use Modules\Inventory\Application\Mappers\ProductDetailMapper;
use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\ValueObjects\Money;
use Modules\Inventory\Domain\ValueObjects\Quantity;
use Modules\Inventory\Domain\ValueObjects\Sku;
use PHPUnit\Framework\TestCase;

final class ProductDetailMapperTest extends TestCase
{
    public function test_maps_domain_product_model_to_array(): void
    {
        $product = Product::create(
            sku: Sku::fromString('SSD-NVME-1TB'),
            name: 'Disco Solido NVMe 1TB',
            category: 'Almacenamiento',
            initialStock: Quantity::fromInteger(10),
            minimumStock: Quantity::fromInteger(2),
            purchasePrice: Money::fromFloat(75.50),
            description: 'PCIe 4.0 alta velocidad',
            brand: 'Samsung',
            model: '980 Pro',
            unitOfMeasure: 'UNIDAD',
            location: 'Estante B2'
        );

        $array = ProductDetailMapper::toArray($product);

        $this->assertSame('SSD-NVME-1TB', $array['sku']);
        $this->assertSame('Disco Solido NVMe 1TB', $array['name']);
        $this->assertSame('Almacenamiento', $array['category']);
        $this->assertSame(10, $array['current_stock']);
        $this->assertSame(2, $array['minimum_stock']);
        $this->assertFalse($array['is_low_stock']);
        $this->assertSame(75.50, $array['purchase_price']);
        $this->assertSame('Samsung', $array['brand']);
        $this->assertSame('Estante B2', $array['location']);
    }

    public function test_maps_generic_object_to_array(): void
    {
        $generic = (object) [
            'id' => 42,
            'sku' => 'CAB-HDMI-2M',
            'name' => 'Cable HDMI 2m',
            'category' => 'Cables',
            'description' => 'HDMI 2.1 8K',
            'brand' => 'UGreen',
            'model' => 'HD104',
            'unit_of_measure' => 'PIEZA',
            'current_stock' => 1,
            'minimum_stock' => 5,
            'purchase_price' => 12.0,
            'location' => 'Bodega 1',
        ];

        $array = ProductDetailMapper::toArray($generic);

        $this->assertSame(42, $array['id']);
        $this->assertSame('CAB-HDMI-2M', $array['sku']);
        $this->assertTrue($array['is_low_stock']);
        $this->assertSame(12.0, $array['purchase_price']);
    }
}
