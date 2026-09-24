<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class DeleteTicketUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository
    ) {
    }

    public function execute(int $id): bool
    {
        return $this->repository->deleteTicket($id);
    }
}
