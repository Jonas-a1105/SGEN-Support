<?php

declare(strict_types=1);

namespace Modules\Employee\Domain\Exceptions;

use DomainException;

/**
 * Regla #27 (offboarding): un empleado con custodias activas no puede
 * desvincularse hasta recuperar cada equipo a su cargo. La cadena
 * custodial es la única fuente de verdad patrimonial.
 */
final class EmployeeHasActiveCustodiesException extends DomainException
{
    /** @param  list<int>  $custodiaIds */
    public static function forEmployee(string $nombre, array $custodiaIds): self
    {
        $total = count($custodiaIds);
        $lista = implode(', ', array_map(static fn (int $id): string => "#{$id}", array_slice($custodiaIds, 0, 10)));
        $resto = $total > 10 ? '…' : '';

        return new self(
            "Operación bloqueada: [{$nombre}] tiene {$total} custodia(s) activas ({$lista}{$resto}). "
            .'Recupere y cierre cada custodia antes de desvincular al empleado.'
        );
    }
}
