<?php

declare(strict_types=1);

namespace Modules\User\Application\DTOs;

final readonly class UpdateUserDTO
{
    public function __construct(
        public string $username,
        public ?string $password,
        public string $rol,
        public ?int $departamentoId,
        public ?int $empleadoId
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            username: (string) ($data['username'] ?? ''),
            password: !empty($data['password']) ? (string) $data['password'] : null,
            rol: (string) ($data['rol'] ?? 'tecnico'),
            departamentoId: !empty($data['departamento_id']) ? (int) $data['departamento_id'] : null,
            empleadoId: !empty($data['empleado_id']) ? (int) $data['empleado_id'] : null
        );
    }
}
