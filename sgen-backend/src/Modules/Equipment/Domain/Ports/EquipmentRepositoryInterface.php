<?php

declare(strict_types=1);

namespace Modules\Equipment\Domain\Ports;

use Modules\Equipment\Application\DTOs\CreateEquipmentDTO;
use Modules\Equipment\Application\DTOs\EquipmentDetailDTO;
use Modules\Equipment\Application\DTOs\EquipmentKpisDTO;
use Modules\Equipment\Application\DTOs\EquipmentListItemDTO;
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
     * Traslada un equipo entre departamentos validando el origen.
     */
    public function transfer(TransferEquipmentDTO $dto): void;

    public function update(int $id, UpdateEquipmentDTO $dto): void;

    public function delete(int $id): void;

    /**
     * Cadena custodial (Módulo 12):
     * - assignCustody cierra la custodia vigente y abre la nueva en una sola
     *   transacción (una activa por equipo, respaldado por índice parcial).
     * - signCustody sella la firma del custodio con evidencia probatoria,
     *   una sola vez y de forma inmutable.
     */
    public function assignCustody(int $equipoId, ?int $empleadoId, ?int $actorUserId, ?string $motivo = null): void;

    public function signCustody(int $equipoId, string $signatureData, string $ipAddress, ?string $userAgent): void;

    /** @return array<string, mixed>|null custodia vigente del equipo. */
    public function currentCustody(int $equipoId): ?array;

    /** @return list<array<string, mixed>> historial completo, más reciente primero. */
    public function custodyHistory(int $equipoId): array;

    /** @return list<int> custodias ACTIVAS donde el empleado es responsable (#27). */
    public function activeCustodyIdsOfEmployee(int $empleadoId): array;

    /**
     * @return array{departments: array<int, array{id: int, nombre: string}>, employees: array<int, array{id: int, nombre_completo: string, cargo: ?string}>}
     */
    public function getFormOptions(): array;
}
