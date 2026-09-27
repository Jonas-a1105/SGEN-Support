<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class RateTicketUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository
    ) {
    }

    public function execute(int $ticketId, string $rating, ?string $comment, int $actingUserId): bool
    {
        return $this->repository->rateTicket($ticketId, $rating, $comment, $actingUserId);
    }
}
