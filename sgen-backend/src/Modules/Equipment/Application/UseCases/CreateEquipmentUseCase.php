<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\UseCases;

use Modules\Equipment\Application\DTOs\CreateEquipmentDTO;
use Modules\Equipment\Domain\Ports\EquipmentRepositoryInterface;

final class CreateEquipmentUseCase
{
    public function __construct(
        private readonly EquipmentRepositoryInterface $repository
    ) {
    }

    public function execute(CreateEquipmentDTO $dto): int
    {
        return $this->repository->create($dto);
    }
}
