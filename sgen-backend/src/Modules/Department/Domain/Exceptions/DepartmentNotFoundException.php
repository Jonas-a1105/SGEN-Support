<?php

declare(strict_types=1);

namespace Modules\Department\Domain\Exceptions;

use DomainException;

final class DepartmentNotFoundException extends DomainException
{
    public static function withId(int|string $id): self
    {
        return new self("El departamento con ID [{$id}] no fue encontrado en el sistema.");
    }
}
