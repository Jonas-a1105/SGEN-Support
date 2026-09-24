<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Exceptions;

use DomainException;

final class InsufficientStockException extends DomainException
{
    public static function forProduct(string $sku, int $currentStock, int $requestedStock): self
    {
        return new self(
            sprintf(
                'Stock insuficiente para el producto [%s]. Stock actual: %d, solicitado: %d.',
                $sku,
                $currentStock,
                $requestedStock
            )
        );
    }
}
