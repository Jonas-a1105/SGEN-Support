<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Exceptions;

use DomainException;

final class TicketNotFoundException extends DomainException
{
    public static function withId(int|string $id): self
    {
        return new self("No se encontró el ticket de soporte con identificador [{$id}].");
    }
}
