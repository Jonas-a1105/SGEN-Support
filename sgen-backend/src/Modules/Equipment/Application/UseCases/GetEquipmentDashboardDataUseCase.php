<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\UseCases;

use Modules\Equipment\Domain\Ports\EquipmentRepositoryInterface;

final class GetEquipmentDashboardDataUseCase
{
    public function __construct(
        private readonly EquipmentRepositoryInterface $repository
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters = []): array
    {
        $kpis = $this->repository->getKpis();
        $equipos = $this->repository->list($filters);
        $options = $this->repository->getFormOptions();

        return [
            'kpis' => $kpis->toArray(),
            'equipos' => array_map(fn($item) => $item->toArray(), $equipos),
            'options' => $options,
            'filters' => $filters,
        ];
    }
}
