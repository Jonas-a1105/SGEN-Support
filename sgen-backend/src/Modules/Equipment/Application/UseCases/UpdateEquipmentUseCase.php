<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\UseCases;

use Modules\Equipment\Application\DTOs\UpdateEquipmentDTO;
use Modules\Equipment\Domain\Ports\EquipmentRepositoryInterface;

final class UpdateEquipmentUseCase
{
    public function __construct(
        private readonly EquipmentRepositoryInterface $repository
    ) {
    }

    public function execute(int $id, UpdateEquipmentDTO $dto): void
    {
        $this->repository->update($id, $dto);
    }
}
