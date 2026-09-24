<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class ReassignTechnicianUseCase
{
    public function __construct(
        private readonly SupportRepositoryInterface $repository
    ) {
    }

    public function execute(int $ticketId, int $employeeId): bool
    {
        return $this->repository->reassignTechnician($ticketId, $employeeId);
    }
}
