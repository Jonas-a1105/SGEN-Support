<?php

declare(strict_types=1);

namespace Modules\User\Domain\Exceptions;

use DomainException;

/**
 * Regla de integridad referencial (checklist #43): un usuario con historial
 * operativo (movimientos de inventario, comentarios en tickets…) no puede
 * eliminarse, porque la trazabilidad apunta a su identidad. La vía correcta
 * es conservar el registro y bloquear su acceso, no destruir la evidencia.
 */
final class UserHasOperationalHistoryException extends DomainException
{
    /**
     * @param  array<string, int>  $referencias  tabla/origen legible → filas vinculadas
     */
    public static function forUser(string $username, array $referencias): self
    {
        $detalle = collect($referencias)
            ->map(static fn (int $filas, string $origen): string => "{$filas} en {$origen}")
            ->implode(', ');

        return new self(
            "Operación bloqueada: el usuario [{$username}] tiene historial operativo ({$detalle}). "
            .'No puede eliminarse sin romper la trazabilidad; desactívelo o reasigne su carga.'
        );
    }
}
