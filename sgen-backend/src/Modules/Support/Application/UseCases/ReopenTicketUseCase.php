<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Support\Domain\Events\TicketReopened;
use Modules\Support\Domain\Ports\DomainEventDispatcher;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

/**
 * Reapertura de tickets: SOLO válida sobre tickets con estado "resuelto",
 * revirtiendo temporalmente a "en_proceso" para una nueva atención.
 * La legalidad de la transición la verifica el repositorio contra la
 * máquina de estados del dominio; aquí solo se publica el hecho consumado.
 */
final class ReopenTicketUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository,
        private readonly DomainEventDispatcher $events
    ) {}

    public function execute(int $ticketId, string $motivo, ?int $userId = null): bool
    {
        $success = $this->repository->reopenTicket($ticketId, $motivo, $userId);

        if ($success) {
            $ticket = DB::table('soportes')->where('id', $ticketId)->first(['id', 'titulo', 'empleado_id']);

            $this->events->dispatch(new TicketReopened(
                $ticketId,
                (string) ($ticket->titulo ?? ''),
                $motivo,
                $ticket->empleado_id !== null ? (int) $ticket->empleado_id : null,
            ));
        }

        return $success;
    }
}
