<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\UseCases;

use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;

final class DeleteMaintenanceUseCase
{
    public function __construct(
        private MaintenanceRepositoryInterface $repository
    ) {}

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
