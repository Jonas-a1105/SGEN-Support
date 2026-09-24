<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Application\DTOs\CreateTicketDTO;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class CreateTicketUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository
    ) {
    }

    public function execute(CreateTicketDTO $dto, ?int $userId = null): int
    {
        return $this->repository->createTicket($dto, $userId);
    }
}
