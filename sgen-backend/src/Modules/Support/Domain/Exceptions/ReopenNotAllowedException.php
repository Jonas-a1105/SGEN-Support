<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Exceptions;

use DomainException;

/**
 * Regla de reapertura (checklist #31): solo dentro de la ventana de días
 * configurada desde la resolución, y solo la puede pedir el solicitante
 * (o un técnico/administrador actuando en su nombre).
 */
final class ReopenNotAllowedException extends DomainException
{
    public static function expiredWindow(int $ticketId, int $dias): self
    {
        return new self(
            "El ticket #{$ticketId} ya no puede reabrirse: la ventana de reapertura es de "
            ."{$dias} días desde la resolución. Cree un ticket nuevo haciendo referencia a este."
        );
    }
}
