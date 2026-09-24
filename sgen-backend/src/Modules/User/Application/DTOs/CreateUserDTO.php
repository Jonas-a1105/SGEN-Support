<?php

declare(strict_types=1);

namespace Modules\User\Application\DTOs;

final readonly class CreateUserDTO
{
    public function __construct(
        public string $username,
        public string $password,
        public string $rol,
        public ?int $departamentoId,
        public ?int $empleadoId,
        public ?string $empleadoNombre = null,
        public ?string $empleadoEmail = null
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            username: (string) ($data['username'] ?? ''),
            password: (string) ($data['password'] ?? ''),
            rol: (string) ($data['rol'] ?? 'tecnico'),
            departamentoId: !empty($data['departamento_id']) ? (int) $data['departamento_id'] : null,
            empleadoId: !empty($data['empleado_id']) ? (int) $data['empleado_id'] : null,
            empleadoNombre: isset($data['empleado_nombre']) ? (string) $data['empleado_nombre'] : null,
            empleadoEmail: isset($data['empleado_email']) ? (string) $data['empleado_email'] : null
        );
    }
}
