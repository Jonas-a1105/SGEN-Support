<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\UseCases;

use Modules\Equipment\Application\DTOs\TransferEquipmentDTO;
use Modules\Equipment\Domain\Ports\EquipmentRepositoryInterface;

/**
 * Traslada un equipo de un departamento a otro.
 * Valida que el equipo pertenezca al departamento de origen
 * y que el destino sea distinto; deja el motivo auditable.
 */
final class TransferEquipmentUseCase
{
    public function __construct(
        private readonly EquipmentRepositoryInterface $repository
    ) {
    }

    public function execute(TransferEquipmentDTO $dto): void
    {
        if ($dto->departamentoOrigenId === $dto->departamentoDestinoId) {
            throw new \InvalidArgumentException('El departamento de destino debe ser distinto al de origen.');
        }

        $this->repository->transfer($dto);
    }
}
