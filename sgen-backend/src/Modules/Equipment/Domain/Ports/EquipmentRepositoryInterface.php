<?php

declare(strict_types=1);

namespace Modules\Equipment\Domain\Ports;

use Modules\Equipment\Application\DTOs\CreateEquipmentDTO;
use Modules\Equipment\Application\DTOs\EquipmentDetailDTO;
use Modules\Equipment\Application\DTOs\EquipmentKpisDTO;
use Modules\Equipment\Application\DTOs\EquipmentListItemDTO;
use Modules\Equipment\Application\DTOs\RegisterEquipmentDTO;
use Modules\Equipment\Application\DTOs\TransferEquipmentDTO;
use Modules\Equipment\Application\DTOs\UpdateEquipmentDTO;

interface EquipmentRepositoryInterface
{
    public function getKpis(): EquipmentKpisDTO;

    /**
     * @param  array<string, mixed>  $filters
     * @return EquipmentListItemDTO[]
     */
    public function list(array $filters = []): array;

    public function findById(int $id): ?EquipmentDetailDTO;

    public function getCompleteDetail(int $id): ?EquipmentDetailDTO;

    public function create(CreateEquipmentDTO $dto): int;

    /**
     * Alta rápida con unicidad de código patrimonial y serial.
     */
    public function register(RegisterEquipmentDTO $dto): int;

    /**
     * Traslada un equipo entre departamentos validando el origen.
     */
    public function transfer(TransferEquipmentDTO $dto): void;

    public function update(int $id, UpdateEquipmentDTO $dto): void;

    public function delete(int $id): void;

    /**
     * @return array{departments: array<int, array{id: int, nombre: string}>, employees: array<int, array{id: int, nombre_completo: string, cargo: ?string}>}
     */
    public function getFormOptions(): array;
}
