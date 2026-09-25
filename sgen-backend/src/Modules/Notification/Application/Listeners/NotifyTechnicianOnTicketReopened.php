<?php

declare(strict_types=1);

namespace Modules\Notification\Application\Listeners;

use Illuminate\Support\Facades\Mail;
use Modules\Notification\Application\UseCases\CreateNotificationUseCase;
use Modules\Notification\Domain\Enums\NotificationType;
use Modules\Notification\Infrastructure\Mail\TicketNotificationMail;
use Modules\Support\Domain\Events\TicketReopened;
use Modules\User\Domain\Ports\UserRepositoryInterface;

final class NotifyTechnicianOnTicketReopened
{
    public function __construct(
        private readonly CreateNotificationUseCase $createNotification,
        private readonly UserRepositoryInterface $users
    ) {}

    public function handle(TicketReopened $event): void
    {
        if (! $event->empleadoId) {
            return;
        }

        try {
            $techUserId = $this->users->findByEmpleadoId($event->empleadoId)?->id();

            if (! $techUserId) {
                return;
            }

            $this->createNotification->execute(
                (int) $techUserId,
                NotificationType::TICKET_ESTADO_CAMBIADO,
                'Ticket Reabierto',
                "El ticket #{$event->ticketId} ({$event->ticketTitulo}) fue reabierto: {$event->motivo}",
                "/soportes/{$event->ticketId}"
            );

            // Canal correo: solo si el técnico tiene email registrado.
            $email = $this->users->findById((int) $techUserId)?->email();
            if ($email) {
                Mail::to($email)->send(new TicketNotificationMail(
                    'Ticket reabierto',
                    "El ticket #{$event->ticketId} ({$event->ticketTitulo}) fue reabierto. Motivo: {$event->motivo}",
                    "/soportes/{$event->ticketId}",
                ));
            }
        } catch (\Throwable $e) {
            // La notificación no detiene la operación, pero jamás falla en silencio.
            report($e);
        }
    }
}
