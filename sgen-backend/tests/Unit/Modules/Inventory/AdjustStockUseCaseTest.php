<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Inventory;

use Modules\Inventory\Application\DTOs\StockAdjustmentDTO;
use Modules\Inventory\Application\UseCases\AdjustStockUseCase;
use Modules\Inventory\Domain\Enums\MovementType;
use Modules\Inventory\Domain\Models\Product;
use Modules\Inventory\Domain\Ports\InventoryNotificationInterface;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;
use Modules\Inventory\Domain\ValueObjects\Money;
use Modules\Inventory\Domain\ValueObjects\Quantity;
use Modules\Inventory\Domain\ValueObjects\Sku;
use PHPUnit\Framework\TestCase;

final class AdjustStockUseCaseTest extends TestCase
{
    public function test_adjust_stock_use_case_saves_product_and_notifies_when_low(): void
    {
        $product = new Product(
            id: 10,
            sku: Sku::fromString('TECL-USB-LOGI'),
            name: 'Teclado Logitech',
            category: 'Periféricos',
            description: null,
            brand: 'Logitech',
            model: 'K120',
            unitOfMeasure: 'uds',
            currentStock: Quantity::fromInteger(6),
            minimumStock: Quantity::fromInteger(5),
            location: 'Estante 3',
            purchasePrice: Money::fromFloat(12.0)
        );

        $savedProduct = null;
        $repo = new class($product, $savedProduct) implements ProductRepositoryInterface
        {
            public function __construct(private Product $prod, public ?Product &$saved) {}

            public function findById(int $id): ?Product
            {
                return $this->prod;
            }

            public function findBySku(string $sku): ?Product
            {
                return null;
            }

            public function paginate(array $filters = [], int $perPage = 15, int $page = 1): array
            {
                return [];
            }

            public function save(Product $p): Product
            {
                $this->saved = $p;

                return $p;
            }

            public function getKpis(): array
            {
                return [];
            }
        };

        $notified = false;
        $notifier = new class($notified) implements InventoryNotificationInterface
        {
            public function __construct(public bool &$notifiedRef) {}

            public function notifyLowStock(Product $product): void
            {
                $this->notifiedRef = true;
            }
        };

        $useCase = new AdjustStockUseCase($repo, $notifier);

        $dto = new StockAdjustmentDTO(
            productId: 10,
            userId: 1,
            type: MovementType::SALIDA,
            quantity: 3,
            reason: 'Entrega a usuario'
        );

        $result = $useCase->execute($dto);

        // Stock fell from 6 to 3, which is <= minimumStock (5), so notification triggered!
        $this->assertSame(3, $result->currentStock()->value());
        $this->assertTrue($notified);
        $this->assertNotNull($repo->saved);
        $this->assertSame(3, $repo->saved->currentStock()->value());
    }
}
