<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class BulkDeleteTicketsUseCase
{
    public function __construct(
        private SupportRepositoryInterface $repository
    ) {}

    public function execute(array $ticketIds): int
    {
        return $this->repository->bulkDeleteTickets($ticketIds);
    }
}
