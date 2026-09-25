<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Notification\Application\UseCases\CreateNotificationUseCase;
use Modules\Notification\Domain\Enums\NotificationType;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class ReopenTicketUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository,
        private readonly CreateNotificationUseCase $notificationUseCase
    ) {}

    public function execute(int $ticketId, string $motivo, ?int $userId = null): bool
    {
        $ticket = DB::table('soportes')->where('id', $ticketId)->first();
        if ($ticket === null) {
            return false;
        }

        $success = $this->repository->reopenTicket($ticketId, $motivo, $userId);

        if ($success && $ticket->empleado_id) {
            $techUserId = DB::table('usuarios')->where('empleado_id', $ticket->empleado_id)->value('id')
                       ?? DB::table('empleados')->where('id', $ticket->empleado_id)->value('usuario_id');

            if ($techUserId) {
                try {
                    $this->notificationUseCase->execute(
                        (int) $techUserId,
                        NotificationType::TICKET_ESTADO_CAMBIADO,
                        'Ticket Reabierto',
                        "El ticket #{$ticketId} ({$ticket->titulo}) fue reabierto: {$motivo}",
                        "/soportes/{$ticketId}"
                    );
                } catch (\Throwable) {
                    // Silencioso ante fallos puntuales
                }
            }
        }

        return $success;
    }
}
