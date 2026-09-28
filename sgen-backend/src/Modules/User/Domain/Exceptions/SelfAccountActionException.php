<?php

declare(strict_types=1);

namespace Modules\User\Domain\Exceptions;

use DomainException;

/**
 * Nadie puede quitarse el propio acceso en sesión: desactivarse o
 * eliminarse a uno mismo dejaría al operador actual bloqueado fuera
 * del sistema (y, si es administrador, sin rescate administrativo).
 */
final class SelfAccountActionException extends DomainException
{
    public static function deactivating(): self
    {
        return new self('Operación bloqueada: no puedes desactivar tu propia cuenta en sesión. Pide a otro administrador que lo haga.');
    }

    public static function deleting(): self
    {
        return new self('Operación bloqueada: no puedes eliminar tu propia cuenta en sesión. Pide a otro administrador que lo haga.');
    }

    /**
     * Cambiarse el propio rol permite auto-promoción o auto-degradación con
     * el rol Spatie desincronizado; lo decide otro administrador.
     */
    public static function roleChange(): self
    {
        return new self('Operación bloqueada: no puedes cambiar tu propio rol en sesión. Pide a otro administrador que lo haga.');
    }
}
