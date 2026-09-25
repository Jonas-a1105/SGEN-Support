<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Modules\Support\Domain\Events\TicketAssigned;
use Modules\Support\Domain\Ports\DomainEventDispatcher;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class ReassignTechnicianUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository,
        private readonly DomainEventDispatcher $events
    ) {}

    public function execute(int $ticketId, int $employeeId): bool
    {
        $success = $this->repository->reassignTechnician($ticketId, $employeeId);

        if ($success) {
            $titulo = (string) (DB::table('soportes')->where('id', $ticketId)->value('titulo') ?? '');

            $this->events->dispatch(new TicketAssigned($ticketId, $employeeId, $titulo));
        }

        return $success;
    }
}
