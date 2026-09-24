<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\UseCases;

use Modules\Equipment\Application\DTOs\EquipmentDetailDTO;
use Modules\Equipment\Domain\Ports\EquipmentRepositoryInterface;

final class GetEquipmentDetailUseCase
{
    public function __construct(
        private readonly EquipmentRepositoryInterface $repository
    ) {
    }

    public function execute(int $id): ?EquipmentDetailDTO
    {
        return $this->repository->getCompleteDetail($id);
    }
}
