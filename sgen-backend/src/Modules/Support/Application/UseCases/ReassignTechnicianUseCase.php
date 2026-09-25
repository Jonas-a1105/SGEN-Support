<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Notification\Application\UseCases\CreateNotificationUseCase;
use Modules\Notification\Domain\Enums\NotificationType;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class ReassignTechnicianUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository,
        private readonly CreateNotificationUseCase $notificationUseCase
    ) {
    }

    public function execute(int $ticketId, int $employeeId): bool
    {
        $success = $this->repository->reassignTechnician($ticketId, $employeeId);

        if ($success) {
            $techUserId = DB::table('usuarios')->where('empleado_id', $employeeId)->value('id')
                       ?? DB::table('empleados')->where('id', $employeeId)->value('usuario_id');

            if ($techUserId) {
                $ticket = DB::table('soportes')->where('id', $ticketId)->first(['id', 'titulo']);
                try {
                    $this->notificationUseCase->execute(
                        (int) $techUserId,
                        NotificationType::TICKET_ASIGNADO,
                        'Ticket Asignado',
                        "Se te ha asignado el ticket #{$ticketId}: " . ($ticket?->titulo ?? ''),
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
