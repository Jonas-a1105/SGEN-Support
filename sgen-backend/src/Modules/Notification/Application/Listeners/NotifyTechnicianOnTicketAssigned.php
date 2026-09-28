<?php

declare(strict_types=1);

namespace Modules\Notification\Application\Listeners;

use Illuminate\Support\Facades\Mail;
use Modules\Notification\Application\Services\EmailThrottler;
use Modules\Notification\Application\UseCases\CreateNotificationUseCase;
use Modules\Notification\Domain\Enums\NotificationType;
use Modules\Notification\Infrastructure\Mail\TicketNotificationMail;
use Modules\Support\Domain\Events\TicketAssigned;
use Modules\User\Domain\Ports\UserRepositoryInterface;

/**
 * El módulo Notification escucha hechos del dominio Support: Support ya no
 * conoce ni importa nada de Notification (desacople por eventos de dominio).
 */
final class NotifyTechnicianOnTicketAssigned
{
    public function __construct(
        private readonly CreateNotificationUseCase $createNotification,
        private readonly UserRepositoryInterface $users
    ) {}

    public function handle(TicketAssigned $event): void
    {
        try {
            $techUser = $this->users->findByEmpleadoId($event->empleadoId);
            $techUserId = $techUser?->id();

            if (! $techUserId) {
                return;
            }

            $this->createNotification->execute(
                (int) $techUserId,
                NotificationType::TICKET_ASIGNADO,
                'Ticket Asignado',
                "Se te ha asignado el ticket #{$event->ticketId}: {$event->ticketTitulo}",
                "/soportes/{$event->ticketId}"
            );

            // Canal correo: solo si el usuario técnico tiene email registrado.
            // El cooldown se marca DESPUÉS del envío: un fallo SMTP no lo consume.
            $email = $this->users->findById((int) $techUserId)?->email();
            if ($email && EmailThrottler::canSend((int) $techUserId, $email, 'ticket_asignado')) {
                Mail::to($email)->send(new TicketNotificationMail(
                    'Ticket asignado',
                    "Se te ha asignado el ticket #{$event->ticketId}: {$event->ticketTitulo}",
                    "/soportes/{$event->ticketId}",
                ));
                EmailThrottler::markSent((int) $techUserId, $email, 'ticket_asignado', 'Ticket asignado');
            }
        } catch (\Throwable $e) {
            // La notificación no detiene la operación, pero jamás falla en silencio.
            report($e);
        }
    }
}
