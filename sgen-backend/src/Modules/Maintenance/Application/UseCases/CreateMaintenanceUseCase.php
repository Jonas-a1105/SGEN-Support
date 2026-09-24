<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\UseCases;

use Modules\Maintenance\Application\DTOs\CreateMaintenanceDTO;
use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;

final class CreateMaintenanceUseCase
{
    public function __construct(
        private MaintenanceRepositoryInterface $repository
    ) {}

    public function execute(CreateMaintenanceDTO $dto, ?int $userId = null): int
    {
        return $this->repository->create($dto, $userId);
    }
}
