<?php

declare(strict_types=1);

namespace Modules\Support\Domain\ValueObjects;

use InvalidArgumentException;

final class TicketId
{
    public function __construct(
        private readonly int $value
    ) {
        if ($this->value <= 0) {
            throw new InvalidArgumentException("El identificador del ticket debe ser un entero positivo. Dado: {$this->value}");
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function formatted(): string
    {
        return 'T-' . $this->value;
    }

    public function code(): string
    {
        return '#T-' . $this->value;
    }

    public static function fromRaw(int|string $raw): self
    {
        $clean = preg_replace('/[^0-9]/', '', (string) $raw);
        $intVal = (int) $clean;

        return new self($intVal);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
