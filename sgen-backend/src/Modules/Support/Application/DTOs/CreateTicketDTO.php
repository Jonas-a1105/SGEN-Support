<?php

declare(strict_types=1);

namespace Modules\Support\Application\DTOs;

final class CreateTicketDTO
{
    public function __construct(
        public readonly string $titulo,
        public readonly string $descripcion,
        public readonly int $equipoId,
        public readonly ?int $categoriaId = null,
        public readonly ?int $empleadoId = null,
        public readonly string $prioridad = 'media',
        public readonly string $estado = 'pendiente',
        public readonly ?string $solicitante = null,
        public readonly ?string $departamento = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            titulo: (string) ($data['titulo'] ?? $data['title'] ?? ''),
            descripcion: (string) ($data['descripcion'] ?? $data['description'] ?? $data['titulo'] ?? ''),
            equipoId: (int) ($data['equipo_id'] ?? $data['equipment_id'] ?? 238),
            categoriaId: isset($data['categoria_id']) ? (int) $data['categoria_id'] : null,
            empleadoId: isset($data['empleado_id']) ? (int) $data['empleado_id'] : null,
            prioridad: (string) ($data['prioridad'] ?? $data['priority'] ?? 'media'),
            estado: (string) ($data['estado'] ?? $data['status'] ?? 'pendiente'),
            solicitante: isset($data['solicitante']) ? (string) $data['solicitante'] : null,
            departamento: isset($data['departamento']) ? (string) $data['departamento'] : null
        );
    }
}
