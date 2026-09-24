<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Application\DTOs\UpdateTicketDTO;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class UpdateTicketUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository
    ) {
    }

    public function execute(int $id, UpdateTicketDTO $dto): bool
    {
        return $this->repository->updateTicket($id, $dto);
    }
}
