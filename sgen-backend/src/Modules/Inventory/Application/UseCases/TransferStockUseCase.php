<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\UseCases;

use Modules\Inventory\Application\DTOs\TransferStockDTO;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;

final readonly class TransferStockUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    public function execute(TransferStockDTO $dto, int $userId): void
    {
        $this->repository->transferStock($dto, $userId);
    }
}
