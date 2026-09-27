<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Exceptions;

use DomainException;

/**
 * La firma de conformidad tiene valor probatorio: una vez capturada con
 * su hash, IP y fecha, jamás se sustituye. Si el solicitante firmó por
 * error, el ticket se reabre formalmente y se vuelve a resolver.
 */
final class SignatureAlreadyRegisteredException extends DomainException
{
    public static function forTicket(int $ticketId): self
    {
        return new self(
            "El ticket #{$ticketId} ya tiene una firma registrada; "
            .'la conformidad firmada es única e inmutable.'
        );
    }
}
