<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Events;

/** Hecho de dominio: el ticket fue resuelto (puede calificarse y autocerrarse). */
final readonly class TicketResolved
{
    public function __construct(
        public int $ticketId,
        public string $ticketTitulo,
        public ?int $usuarioCreacionId,
    ) {}
}
