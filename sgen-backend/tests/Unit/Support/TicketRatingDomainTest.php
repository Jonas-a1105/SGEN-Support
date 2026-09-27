<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use DomainException;
use Modules\Support\Domain\Enums\TicketPriority;
use Modules\Support\Domain\Models\Ticket;
use PHPUnit\Framework\TestCase;

/**
 * Defensa en profundidad en el dominio: la entidad Ticket tampoco admite
 * una segunda calificación aunque alguien saltee la capa de persistencia.
 */
final class TicketRatingDomainTest extends TestCase
{
    public function test_un_ticket_ya_calificado_no_puede_recalificarse(): void
    {
        $ticket = Ticket::create(
            title: 'Impresora sin tóner',
            description: 'Se requiere reposición de consumible',
            priority: TicketPriority::MEDIA,
            creatorUserId: 7
        );

        $ticket->rate('excelente', 'Servicio impecable');

        $this->expectException(DomainException::class);
        $ticket->rate('malo', 'Cambio de opinión');
    }

    public function test_un_ticket_sin_calificar_acepta_su_primera_valoracion(): void
    {
        $ticket = Ticket::create(
            title: 'Red intermitente',
            description: 'Caídas cada ciertos minutos',
            priority: TicketPriority::ALTA,
            creatorUserId: 7
        );

        $ticket->rate('bueno');

        $this->addToAssertionCount(1);
    }
}
