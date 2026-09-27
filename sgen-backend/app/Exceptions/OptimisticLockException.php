<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Conflicto de edición concurrente (checklist #22): la entidad ya cambió
 * desde que el lector la vio. HTTP 409 con mensaje claro para reconciliar.
 */
final class OptimisticLockException extends HttpException
{
    public static function forEntity(string $entidad, int $entidadId): self
    {
        return new self(
            409,
            "Conflicto de edición: {$entidad} #{$entidadId} fue modificado por otra persona "
            .'desde que lo abriste. Cierra y vuelve a abrir la ficha para ver los cambios.'
        );
    }
}
