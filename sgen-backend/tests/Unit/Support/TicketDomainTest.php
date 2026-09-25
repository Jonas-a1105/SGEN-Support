<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use DomainException;
use InvalidArgumentException;
use Modules\Support\Domain\Enums\TicketPriority;
use Modules\Support\Domain\Enums\TicketStatus;
use Modules\Support\Domain\Exceptions\InvalidTicketStatusTransitionException;
use Modules\Support\Domain\Models\Ticket;
use Modules\Support\Domain\ValueObjects\TicketId;
use PHPUnit\Framework\TestCase;

final class TicketDomainTest extends TestCase
{
    public function test_can_instantiate_ticket_id_value_object(): void
    {
        $id = new TicketId(96);
        $this->assertSame(96, $id->value());
        $this->assertSame('T-96', $id->formatted());
        $this->assertSame('#T-96', $id->code());

        $parsed = TicketId::fromRaw('T-125');
        $this->assertSame(125, $parsed->value());
    }

    public function test_invalid_ticket_id_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TicketId(-5);
    }

    public function test_can_instantiate_ticket_with_invariants(): void
    {
        $ticket = Ticket::create(
            title: 'Fallo en switch principal',
            description: 'El switch de distribución del piso 2 no responde.',
            priority: TicketPriority::CRITICA,
            equipmentId: 10,
            assignedTechnicianId: 38
        );

        $this->assertSame('Fallo en switch principal', $ticket->title());
        $this->assertSame(TicketStatus::PENDIENTE, $ticket->status());
        $this->assertSame(TicketPriority::CRITICA, $ticket->priority());
        $this->assertSame(38, $ticket->assignedTechnicianId());
        $this->assertTrue($ticket->isSlaOnTime());
    }

    public function test_empty_title_throws_invalid_argument_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Ticket::create(
            title: '   ',
            description: 'Sin título',
            priority: TicketPriority::MEDIA
        );
    }

    public function test_can_reassign_technician_to_pending_or_process_ticket(): void
    {
        $ticket = Ticket::create(
            title: 'Mantenimiento de servidor',
            description: 'Limpieza física y cambio de pasta térmica',
            priority: TicketPriority::MEDIA,
            assignedTechnicianId: 10
        );

        $ticket->reassignTechnician(20);
        $this->assertSame(20, $ticket->assignedTechnicianId());
    }

    public function test_cannot_reassign_technician_to_resolved_ticket(): void
    {
        $ticket = Ticket::create(
            title: 'Reparación de cable',
            description: 'Cable de red dañado',
            priority: TicketPriority::BAJA,
            assignedTechnicianId: 10
        );

        $ticket->changeStatus(TicketStatus::EN_PROCESO);
        $ticket->changeStatus(TicketStatus::RESUELTO);

        $this->expectException(DomainException::class);
        $ticket->reassignTechnician(30);
    }

    public function test_invalid_status_transition_throws_domain_exception(): void
    {
        $ticket = Ticket::create(
            title: 'Incidencia de correo',
            description: 'No envía correos a clientes',
            priority: TicketPriority::ALTA
        );

        // Un ticket resuelto no puede retroceder directamente a pendiente.
        $ticket->changeStatus(TicketStatus::RESUELTO);

        $this->expectException(InvalidTicketStatusTransitionException::class);
        $ticket->changeStatus(TicketStatus::PENDIENTE);
    }

    public function test_closed_ticket_is_terminal_and_cannot_reopen(): void
    {
        $ticket = Ticket::create(
            title: 'Incidencia de correo',
            description: 'No envía correos a clientes',
            priority: TicketPriority::ALTA
        );

        $ticket->changeStatus(TicketStatus::RESUELTO);
        $ticket->close();
        $this->assertSame(TicketStatus::CERRADO, $ticket->status());
        $this->assertFalse($ticket->isEditable());

        $this->expectException(InvalidTicketStatusTransitionException::class);
        $ticket->reopen('La falla regresó una semana después del cierre.');
    }

    public function test_resolved_ticket_can_reopen_with_motive(): void
    {
        $ticket = Ticket::create(
            title: 'Impresora atascada',
            description: 'El rodillo se traba al imprimir',
            priority: TicketPriority::MEDIA
        );

        $ticket->changeStatus(TicketStatus::EN_PROCESO);
        $ticket->changeStatus(TicketStatus::RESUELTO);
        $ticket->reopen('La falla se volvió a presentar al día siguiente.');

        $this->assertSame(TicketStatus::EN_PROCESO, $ticket->status());
    }

    public function test_reopen_requires_a_motive(): void
    {
        $ticket = Ticket::create(
            title: 'Monitor parpadea',
            description: 'La pantalla parpadea intermitentemente',
            priority: TicketPriority::BAJA
        );

        $ticket->changeStatus(TicketStatus::RESUELTO);

        $this->expectException(InvalidArgumentException::class);
        $ticket->reopen('   ');
    }

    public function test_can_rate_ticket_with_valid_score(): void
    {
        $ticket = Ticket::create(
            title: 'Configuración de impresora',
            description: 'Instalación de drivers en puesto 4',
            priority: TicketPriority::BAJA
        );

        $ticket->rate('excelente', 'Atención rápida y profesional');
        $this->assertSame('excelente', $ticket->rating());
        $this->assertSame('Atención rápida y profesional', $ticket->ratingComment());
        $this->assertNotNull($ticket->ratingDate());
    }

    public function test_invalid_rating_throws_exception(): void
    {
        $ticket = Ticket::create(
            title: 'Configuración de router',
            description: 'Apertura de puertos',
            priority: TicketPriority::MEDIA
        );

        $this->expectException(InvalidArgumentException::class);
        $ticket->rate('invalido_score');
    }

    public function test_priority_sla_hours_and_critical_detection(): void
    {
        $critical = TicketPriority::CRITICA;
        $this->assertTrue($critical->isCritical());
        $this->assertSame(4, $critical->slaHours());

        $baja = TicketPriority::BAJA;
        $this->assertFalse($baja->isCritical());
        $this->assertSame(48, $baja->slaHours());
    }
}
