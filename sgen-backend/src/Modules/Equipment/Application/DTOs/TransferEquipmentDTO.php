<?php

declare(strict_types=1);

namespace Modules\Equipment\Application\DTOs;

/**
 * Traslado de un equipo entre departamentos con motivo auditable.
 */
final class TransferEquipmentDTO
{
    public function __construct(
        public readonly int $equipoId,
        public readonly int $departamentoOrigenId,
        public readonly int $departamentoDestinoId,
        public readonly ?string $motivo = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            equipoId: (int) $data['equipo_id'],
            departamentoOrigenId: (int) $data['departamento_origen_id'],
            departamentoDestinoId: (int) $data['departamento_destino_id'],
            motivo: isset($data['motivo']) && $data['motivo'] !== '' ? (string) $data['motivo'] : null,
        );
    }
}
