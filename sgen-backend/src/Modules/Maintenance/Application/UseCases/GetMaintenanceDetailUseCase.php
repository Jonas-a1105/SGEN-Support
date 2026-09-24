<?php

declare(strict_types=1);

namespace Modules\Maintenance\Application\UseCases;

use Modules\Maintenance\Domain\Ports\MaintenanceRepositoryInterface;

final class GetMaintenanceDetailUseCase
{
    public function __construct(
        private MaintenanceRepositoryInterface $repository
    ) {}

    public function execute(int $id): ?array
    {
        $detail = $this->repository->findById($id);
        
        if ($detail === null) {
            return null;
        }

        return (array) $detail;
    }
}
