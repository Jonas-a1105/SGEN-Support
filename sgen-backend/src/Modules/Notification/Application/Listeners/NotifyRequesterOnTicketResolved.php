<?php

declare(strict_types=1);

namespace Modules\Notification\Application\Listeners;

use Modules\Notification\Application\UseCases\CreateNotificationUseCase;
use Modules\Notification\Domain\Enums\NotificationType;
use Modules\Support\Domain\Events\TicketResolved;

final class NotifyRequesterOnTicketResolved
{
    public function __construct(
        private readonly CreateNotificationUseCase $createNotification
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
        } catch (\Throwable $e) {
            // La notificación no detiene la operación, pero jamás falla en silencio.
            report($e);
        }
    }
}
