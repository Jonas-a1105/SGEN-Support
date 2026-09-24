<?php

declare(strict_types=1);

namespace Modules\Employee\Domain\Models;

use InvalidArgumentException;
use Modules\Employee\Domain\Enums\EmployeeRole;

final class Employee
{
    public function __construct(
        private readonly ?int $id,
        private string $nombre,
        private string $apellido,
        private string $email,
        private ?string $cedula = null,
        private ?string $cargo = null,
        private ?int $departamentoId = null,
        private EmployeeRole $rol = EmployeeRole::CONSULTOR,
        private ?int $usuarioId = null,
    ) {
        $this->ensureInvariants();
    }

    private function ensureInvariants(): void
    {
        if (trim($this->nombre) === '') {
            throw new InvalidArgumentException('El nombre del empleado no puede estar vacío.');
        }

        if (trim($this->apellido) === '') {
            throw new InvalidArgumentException('El apellido del empleado no puede estar vacío.');
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("El correo electrónico [{$this->email}] no es válido.");
        }
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function nombre(): string
    {
        return $this->nombre;
    }

    public function apellido(): string
    {
        return $this->apellido;
    }

    public function fullName(): string
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }

    public function email(): string
    {
        return $this->email;
    }

    public function cedula(): ?string
    {
        return $this->cedula;
    }

    public function cargo(): ?string
    {
        return $this->cargo;
    }

    public function departamentoId(): ?int
    {
        return $this->departamentoId;
    }

    public function rol(): EmployeeRole
    {
        return $this->rol;
    }

    public function usuarioId(): ?int
    {
        return $this->usuarioId;
    }

    public function hasUserAccount(): bool
    {
        return $this->usuarioId !== null;
    }

    public function assignDepartment(int $departamentoId): void
    {
        $this->departamentoId = $departamentoId;
    }

    public function linkUserAccount(int $usuarioId): void
    {
        $this->usuarioId = $usuarioId;
    }

    public function unlinkUserAccount(): void
    {
        $this->usuarioId = null;
    }
}
