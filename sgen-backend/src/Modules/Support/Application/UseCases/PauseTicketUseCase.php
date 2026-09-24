<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class PauseTicketUseCase
{
    public function __construct(
        private SupportRepositoryInterface $repository
    ) {}

    public function execute(int $ticketId): bool
    {
        return $this->repository->pauseTicket($ticketId);
    }
}
