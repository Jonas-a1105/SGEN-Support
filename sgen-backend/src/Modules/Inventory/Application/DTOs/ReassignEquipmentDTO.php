<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\DTOs;

final readonly class ReassignEquipmentDTO
{
    public function __construct(
        public int $equipoId,
        public ?int $departamentoId = null,
        public ?int $empleadoId = null,
        public ?string $ubicacionFisica = null
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            equipoId: (int) $data['equipo_id'],
            departamentoId: ! empty($data['departamento_id']) ? (int) $data['departamento_id'] : null,
            empleadoId: ! empty($data['empleado_id']) ? (int) $data['empleado_id'] : null,
            ubicacionFisica: ! empty($data['ubicacion_fisica']) ? (string) $data['ubicacion_fisica'] : null
        );
    }
}
