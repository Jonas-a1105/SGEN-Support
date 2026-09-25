<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Events;

/** Hecho de dominio: un ticket resuelto fue reabierto con motivo justificado. */
final readonly class TicketReopened
{
    public function __construct(
        public int $ticketId,
        public string $ticketTitulo,
        public string $motivo,
        public ?int $empleadoId,
    ) {}
}
