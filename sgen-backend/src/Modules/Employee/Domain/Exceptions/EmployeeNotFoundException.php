<?php

declare(strict_types=1);

namespace Modules\Employee\Domain\Exceptions;

use DomainException;

final class EmployeeNotFoundException extends DomainException
{
    public static function withId(int|string $id): self
    {
        return new self("El empleado con ID [{$id}] no fue encontrado en el sistema.");
    }
}
