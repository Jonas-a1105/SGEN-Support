<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Exceptions;

use DomainException;

/**
 * Reglas de la valoración de un ticket: es un acto del solicitante,
 * único e inmutable. Cualquier otro actor (incluido el técnico o un
 * coordinador) no puede registrar ni reescribir la calificación.
 */
final class RatingNotAllowedException extends DomainException
{
    public static function notRequester(int $ticketId): self
    {
        return new self("Solo el solicitante puede calificar el ticket #{$ticketId}.");
    }

    public static function alreadyRated(int $ticketId): self
    {
        return new self("El ticket #{$ticketId} ya fue calificado; la valoración es única e inmutable.");
    }
}
