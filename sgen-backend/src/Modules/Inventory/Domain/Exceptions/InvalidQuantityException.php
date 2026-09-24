<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Exceptions;

use InvalidArgumentException;

final class InvalidQuantityException extends InvalidArgumentException
{
    public static function nonPositive(int $value): self
    {
        return new self(sprintf('La cantidad debe ser un entero positivo. Valor recibido: %d.', $value));
    }

    public static function negativeStock(int $value): self
    {
        return new self(sprintf('El stock no puede ser un valor negativo. Valor recibido: %d.', $value));
    }
}
