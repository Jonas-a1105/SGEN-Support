<?php

declare(strict_types=1);

namespace Modules\User\Domain\Exceptions;

use DomainException;

/**
 * Nunca debe existir administración sin administradores: eliminar o
 * degradar al último admin activo dejaría el sistema sin rescate.
 */
final class LastAdminProtectionException extends DomainException
{
    public static function deleting(): self
    {
        return new self('Operación bloqueada: no se puede eliminar al último administrador del sistema. Asigna otro administrador antes de continuar.');
    }

    public static function demoting(): self
    {
        return new self('Operación bloqueada: no se puede quitar el rol de administrador al último administrador activo del sistema.');
    }
}
