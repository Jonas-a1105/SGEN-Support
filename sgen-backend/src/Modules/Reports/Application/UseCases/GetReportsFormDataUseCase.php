<?php

declare(strict_types=1);

namespace Modules\Reports\Application\UseCases;

use Modules\Reports\Domain\Ports\ReportsRepositoryInterface;

final readonly class GetReportsFormDataUseCase
{
    public function __construct(
        private ReportsRepositoryInterface $repository
    ) {}

    public function execute(): array
    {
        return $this->repository->getFormData();
    }
}
