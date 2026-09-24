<?php

declare(strict_types=1);

namespace Modules\User\Domain\Exceptions;

use RuntimeException;

final class UserNotFoundException extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("El usuario con ID {$id} no fue encontrado.");
    }
}
