<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Exceptions;

use DomainException;

final class ProductNotFoundException extends DomainException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Producto con ID [%d] no fue encontrado en el catálogo de inventario.', $id));
    }
}
