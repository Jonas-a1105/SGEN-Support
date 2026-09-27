<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Events;

/** Hecho de dominio: un ticket fue creado (canal multicanal del Módulo 03). */
final readonly class TicketCreated
{
    public function __construct(
        public int $ticketId,
        public string $codigo,
        public string $ticketTitulo,
        public string $tokenPublico,
        public ?int $usuarioCreacionId
    ) {}
}
