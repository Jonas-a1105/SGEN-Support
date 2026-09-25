<?php

declare(strict_types=1);

namespace Modules\Notification\Application\Listeners;

use Illuminate\Support\Facades\DB;
use Modules\Notification\Application\UseCases\CreateNotificationUseCase;
use Modules\Notification\Domain\Enums\NotificationType;
use Modules\Support\Domain\Events\TicketReopened;

final class NotifyTechnicianOnTicketReopened
{
    public function __construct(
        private readonly CreateNotificationUseCase $createNotification
    ) {}

    public function handle(TicketReopened $event): void
    {
        if (! $event->empleadoId) {
            return;
        }

        try {
            $techUserId = DB::table('usuarios')->where('empleado_id', $event->empleadoId)->value('id')
                ?? DB::table('empleados')->where('id', $event->empleadoId)->value('usuario_id');

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
        } catch (\Throwable $e) {
            // La notificación no detiene la operación, pero jamás falla en silencio.
            report($e);
        }
    }
}
