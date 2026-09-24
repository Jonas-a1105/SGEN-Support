<?php

declare(strict_types=1);

namespace Modules\Employee\Application\DTOs;

final class UpdateEmployeeDTO
{
    public function __construct(
        public readonly ?string $nombre = null,
        public readonly ?string $apellido = null,
        public readonly ?string $email = null,
        public readonly ?string $cedula = null,
        public readonly ?string $cargo = null,
        public readonly ?int $departamentoId = null,
        public readonly ?string $rol = null,
        public readonly ?int $usuarioId = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            nombre: isset($data['nombre']) ? (string) $data['nombre'] : (isset($data['firstName']) ? (string) $data['firstName'] : null),
            apellido: isset($data['apellido']) ? (string) $data['apellido'] : (isset($data['lastName']) ? (string) $data['lastName'] : null),
            email: isset($data['email']) ? (string) $data['email'] : null,
            cedula: isset($data['cedula']) ? (string) $data['cedula'] : (isset($data['idDoc']) ? (string) $data['idDoc'] : null),
            cargo: isset($data['cargo']) ? (string) $data['cargo'] : (isset($data['position']) ? (string) $data['position'] : null),
            departamentoId: array_key_exists('departamento_id', $data) ? ($data['departamento_id'] !== null ? (int) $data['departamento_id'] : null) : (array_key_exists('departmentId', $data) ? ($data['departmentId'] !== null ? (int) $data['departmentId'] : null) : null),
            rol: isset($data['rol']) ? (string) $data['rol'] : (isset($data['role']) ? (string) $data['role'] : null),
            usuarioId: array_key_exists('usuario_id', $data) ? ($data['usuario_id'] !== null ? (int) $data['usuario_id'] : null) : (array_key_exists('userId', $data) ? ($data['userId'] !== null ? (int) $data['userId'] : null) : null),
        );
    }
}
