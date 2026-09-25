<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Notification\Application\UseCases\CreateNotificationUseCase;
use Modules\Notification\Domain\Enums\NotificationType;
use Modules\Support\Application\DTOs\UpdateTicketDTO;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class UpdateTicketUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository,
        private readonly CreateNotificationUseCase $notificationUseCase
    ) {
    }

    public function execute(int $id, UpdateTicketDTO $dto): bool
    {
        $success = $this->repository->updateTicket($id, $dto);

        if ($success && $dto->estado === 'resuelto') {
            $ticket = DB::table('soportes')->where('id', $id)->first(['id', 'titulo', 'usuario_creacion_id']);
            if ($ticket?->usuario_creacion_id) {
                try {
                    $this->notificationUseCase->execute(
                        (int) $ticket->usuario_creacion_id,
                        NotificationType::TICKET_ESTADO_CAMBIADO,
                        'Ticket Resuelto',
                        "Tu ticket #{$id} ({$ticket->titulo}) ha sido marcado como resuelto. Ya puedes calificar la atención recibida.",
                        "/soportes/{$id}"
                    );
                } catch (\Throwable) {
                    // Silencioso ante fallos puntuales
                }
            }
        }

        return $success;
    }
}
