<?php

declare(strict_types=1);

namespace Modules\Department\Domain\Exceptions;

use DomainException;

final class EmployeeNotAssignedToDepartmentException extends DomainException
{
    public static function forIds(int $departmentId, int $employeeId): self
    {
        return new self(
            "El empleado [{$employeeId}] no pertenece al departamento [{$departmentId}]; "
            .'no se puede desvincular de un área a la que no está asignado.'
        );
    }
}
