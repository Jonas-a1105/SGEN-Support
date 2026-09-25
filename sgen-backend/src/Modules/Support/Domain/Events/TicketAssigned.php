<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Events;

/** Hecho de dominio: un técnico (empleado) fue asignado/reasignado a un ticket. */
final readonly class TicketAssigned
{
    public function __construct(
        public int $ticketId,
        public int $empleadoId,
        public string $ticketTitulo,
    ) {}
}
