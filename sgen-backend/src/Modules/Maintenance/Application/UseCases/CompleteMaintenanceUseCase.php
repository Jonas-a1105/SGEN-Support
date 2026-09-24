<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\UseCases;

use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;

final class CompleteMaintenanceUseCase
{
    public function __construct(
        private MaintenanceRepositoryInterface $repository
    ) {}

    public function execute(int $id, ?string $observations = null, ?float $cost = null): bool
    {
        return $this->repository->complete($id, $observations, $cost);
    }
}
