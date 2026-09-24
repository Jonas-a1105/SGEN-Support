<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\UseCases;

use Modules\Inventory\Application\DTOs\InventoryKpiDTO;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;

final readonly class GetInventoryKpisUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function execute(): InventoryKpiDTO
    {
        $rawKpis = $this->repository->getKpis();

        return InventoryKpiDTO::fromArray($rawKpis);
    }
}
