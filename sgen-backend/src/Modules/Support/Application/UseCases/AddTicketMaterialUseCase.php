<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class AddTicketMaterialUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository
    ) {
    }

    public function execute(int $ticketId, int $itemId, int $quantity, int $userId): bool
    {
        return $this->repository->addMaterial($ticketId, $itemId, $quantity, $userId);
    }
}
