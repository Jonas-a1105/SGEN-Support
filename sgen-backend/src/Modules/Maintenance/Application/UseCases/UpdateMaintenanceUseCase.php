<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\UseCases;

use Modules\Maintenance\Application\DTOs\UpdateMaintenanceDTO;
use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;

final class UpdateMaintenanceUseCase
{
    public function __construct(
        private MaintenanceRepositoryInterface $repository
    ) {}

    public function execute(int $id, UpdateMaintenanceDTO $dto): bool
    {
        return $this->repository->update($id, $dto);
    }
}
