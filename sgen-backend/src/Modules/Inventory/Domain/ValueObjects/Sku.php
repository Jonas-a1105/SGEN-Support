<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Sku implements Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));

        if (strlen($normalized) < 2 || strlen($normalized) > 50) {
            throw new InvalidArgumentException(
                sprintf('El SKU [%s] es inválido. Debe contener entre 2 y 50 caracteres.', $value)
            );
        }

        $this->value = $normalized;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
