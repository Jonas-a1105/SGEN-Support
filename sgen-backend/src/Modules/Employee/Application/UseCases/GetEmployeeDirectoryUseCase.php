<?php

declare(strict_types=1);

namespace Modules\Employee\Application\UseCases;

use Modules\Employee\Domain\Ports\EmployeeRepositoryInterface;

final class GetEmployeeDirectoryUseCase
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $repository
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters = []): array
    {
        $kpis = $this->repository->getKpis();
        $empleados = $this->repository->list($filters);
        $options = $this->repository->getFormOptions();

        return [
            'kpis' => $kpis->toArray(),
            'empleados' => array_map(fn($item) => $item->toArray(), $empleados),
            'options' => $options,
            'filters' => $filters,
        ];
    }
}
