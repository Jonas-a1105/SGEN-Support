<?php

declare(strict_types=1);

namespace Modules\User\Domain\Exceptions;

use DomainException;

/**
 * Debe ser DomainException: los controladores capturan \DomainException y
 * responden con un error controlado en vez de un 500.
 */
final class UserNotFoundException extends DomainException
{
    public static function withId(int $id): self
    {
        return new self("El usuario con ID {$id} no fue encontrado.");
    }
}
