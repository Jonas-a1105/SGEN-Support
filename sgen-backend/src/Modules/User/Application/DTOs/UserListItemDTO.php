<?php

declare(strict_types=1);

namespace Modules\User\Application\DTOs;

final readonly class UserListItemDTO
{
    public function __construct(
        public int $id,
        public string $username,
        public string $rol,
        public string $rolLabel,
        public ?int $departamentoId,
        public string $departamentoNombre,
        public ?int $empleadoId,
        public ?string $empleadoNombre,
        public ?string $empleadoEmail,
        public string $avatarInitials,
        public bool $isVerifiedEmployee
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'rol' => $this->rol,
            'rol_label' => $this->rolLabel,
            'departamento_id' => $this->departamentoId,
            'departamento_nombre' => $this->departamentoNombre,
            'empleado_id' => $this->empleadoId,
            'empleado_nombre' => $this->empleadoNombre,
            'empleado_email' => $this->empleadoEmail,
            'avatar_initials' => $this->avatarInitials,
            'is_verified_employee' => $this->isVerifiedEmployee,
        ];
    }
}
