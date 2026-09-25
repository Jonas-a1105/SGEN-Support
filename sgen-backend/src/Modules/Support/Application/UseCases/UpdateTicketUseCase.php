<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Support\Application\DTOs\UpdateTicketDTO;
use Modules\Support\Domain\Events\TicketResolved;
use Modules\Support\Domain\Ports\DomainEventDispatcher;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class UpdateTicketUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository,
        private readonly DomainEventDispatcher $events
    ) {}

    public function execute(int $id, UpdateTicketDTO $dto): bool
    {
        $success = $this->repository->updateTicket($id, $dto);

        if ($success && $dto->estado === 'resuelto') {
            $ticket = DB::table('soportes')->where('id', $id)->first(['id', 'titulo', 'usuario_creacion_id']);

            $this->events->dispatch(new TicketResolved(
                $id,
                (string) ($ticket->titulo ?? ''),
                $ticket?->usuario_creacion_id !== null ? (int) $ticket->usuario_creacion_id : null,
            ));
        }

        return $success;
    }
}
