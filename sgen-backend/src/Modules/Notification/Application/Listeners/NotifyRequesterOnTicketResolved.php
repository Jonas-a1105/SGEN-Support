<?php

declare(strict_types=1);

namespace Modules\Notification\Application\Listeners;

use Illuminate\Support\Facades\Mail;
use Modules\Notification\Application\Services\EmailThrottler;
use Modules\Notification\Application\UseCases\CreateNotificationUseCase;
use Modules\Notification\Domain\Enums\NotificationType;
use Modules\Notification\Infrastructure\Mail\TicketNotificationMail;
use Modules\Support\Domain\Events\TicketResolved;
use Modules\User\Domain\Ports\UserRepositoryInterface;

final class NotifyRequesterOnTicketResolved
{
    public function __construct(
        private readonly CreateNotificationUseCase $createNotification,
        private readonly UserRepositoryInterface $users
    ) {}

    public function handle(TicketResolved $event): void
    {
        if (! $event->usuarioCreacionId) {
            return;
        }

        try {
            $this->createNotification->execute(
                $event->usuarioCreacionId,
                NotificationType::TICKET_ESTADO_CAMBIADO,
                'Ticket Resuelto',
                "Tu ticket #{$event->ticketId} ({$event->ticketTitulo}) ha sido marcado como resuelto. Ya puedes calificar la atención recibida.",
                "/soportes/{$event->ticketId}"
            );

            // Canal correo: solo si el solicitante tiene email registrado.
            // El cooldown se marca DESPUÉS del envío: si el SMTP falla, el
            // correo no queda suprimido y podrá reintentarse.
            $email = $this->users->findById($event->usuarioCreacionId)?->email();
            if ($email && EmailThrottler::canSend($event->usuarioCreacionId, $email, 'ticket_resuelto')) {
                Mail::to($email)->send(new TicketNotificationMail(
                    'Ticket resuelto',
                    "Tu ticket #{$event->ticketId} ({$event->ticketTitulo}) ha sido resuelto. Ya puedes calificar la atención recibida.",
                    "/soportes/{$event->ticketId}",
                ));
                EmailThrottler::markSent($event->usuarioCreacionId, $email, 'ticket_resuelto', 'Ticket resuelto');
            }
        } catch (\Throwable $e) {
            // La notificación no detiene la operación, pero jamás falla en silencio.
            report($e);
        }
    }
}
