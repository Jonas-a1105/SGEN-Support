<?php

declare(strict_types=1);

namespace Modules\User\Domain\Exceptions;

use DomainException;

/**
 * No se puede eliminar un usuario cuyo empleado vinculado sigue siendo
 * técnico responsable de tickets activos: la carga operativa debe
 * reasignarse primero para que ningún ticket quede huérfano.
 */
final class UserHasActiveTicketsException extends DomainException
{
    /**
     * @param  list<int>  $ticketIds
     */
    public static function forUser(string $username, array $ticketIds): self
    {
        $total = count($ticketIds);
        $primeros = array_slice($ticketIds, 0, 10);
        $lista = implode(', ', array_map(static fn (int $id): string => "#{$id}", $primeros));
        $resto = $total > count($primeros) ? '…' : '';

        return new self(
            "Operación bloqueada: el usuario [{$username}] es técnico responsable de "
            ."{$total} ticket(s) activos ({$lista}{$resto}). Reasígnalos antes de eliminarlo."
        );
    }
}
