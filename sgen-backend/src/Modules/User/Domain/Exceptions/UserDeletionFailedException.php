<?php

declare(strict_types=1);

namespace Modules\User\Domain\Exceptions;

use DomainException;

/**
 * Defensa en profundidad del borrado de usuarios: cualquier violación de
 * integridad referencial no contemplada por las validaciones de negocio se
 * traduce a un error de dominio legible, jamás a un 500 crudo.
 */
final class UserDeletionFailedException extends DomainException
{
    public static function referenciasVinculadas(string $username): self
    {
        return new self(
            "No se pudo eliminar al usuario [{$username}]: existen registros del sistema "
            .'que aún lo referencian. Revise su historial operativo antes de reintentar.'
        );
    }
}
