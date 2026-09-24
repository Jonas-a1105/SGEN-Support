<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\ValueObjects;

use Modules\Inventory\Domain\Exceptions\InvalidQuantityException;
use Stringable;

final readonly class Quantity implements Stringable
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value < 0) {
            throw InvalidQuantityException::negativeStock($value);
        }

        $this->value = $value;
    }

    public static function fromInteger(int $value): self
    {
        return new self($value);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(self $other): self
    {
        $result = $this->value - $other->value;
        if ($result < 0) {
            throw InvalidQuantityException::negativeStock($result);
        }

        return new self($result);
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLessThan(self $other): bool
    {
        return $this->value < $other->value;
    }

    public function isLessThanOrEqual(self $other): bool
    {
        return $this->value <= $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
