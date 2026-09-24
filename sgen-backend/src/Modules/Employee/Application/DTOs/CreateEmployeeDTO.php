<?php

declare(strict_types=1);

namespace Modules\Employee\Application\DTOs;

final class CreateEmployeeDTO
{
    public function __construct(
        public readonly string $nombre,
        public readonly string $apellido,
        public readonly string $email,
        public readonly ?string $cedula = null,
        public readonly ?string $cargo = null,
        public readonly ?int $departamentoId = null,
        public readonly string $rol = 'consultor',
        public readonly ?int $usuarioId = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            nombre: (string) ($data['nombre'] ?? $data['firstName'] ?? ''),
            apellido: (string) ($data['apellido'] ?? $data['lastName'] ?? ''),
            email: (string) ($data['email'] ?? ''),
            cedula: isset($data['cedula']) ? (string) $data['cedula'] : (isset($data['idDoc']) ? (string) $data['idDoc'] : null),
            cargo: isset($data['cargo']) ? (string) $data['cargo'] : (isset($data['position']) ? (string) $data['position'] : null),
            departamentoId: isset($data['departamento_id']) ? (int) $data['departamento_id'] : (isset($data['departmentId']) ? (int) $data['departmentId'] : null),
            rol: (string) ($data['rol'] ?? $data['role'] ?? 'consultor'),
            usuarioId: isset($data['usuario_id']) ? (int) $data['usuario_id'] : (isset($data['userId']) ? (int) $data['userId'] : null),
        );
    }
}
