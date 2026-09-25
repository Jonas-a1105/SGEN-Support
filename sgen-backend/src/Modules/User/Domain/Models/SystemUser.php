<?php

declare(strict_types=1);

namespace Modules\User\Domain\Models;

use InvalidArgumentException;
use Modules\User\Domain\Enums\UserRole;

final class SystemUser
{
    private function __construct(
        private ?int $id,
        private string $username,
        private ?string $password,
        private UserRole $role,
        private string $theme,
        private ?int $employeeId,
        private ?int $departmentId,
        private ?string $email = null
    ) {
        $trimmedUsername = trim($this->username);
        if ($trimmedUsername === '') {
            throw new InvalidArgumentException('El nombre de usuario no puede estar vacío.');
        }
    }

    public static function create(
        string $username,
        ?string $password,
        UserRole $role = UserRole::CONSULTOR,
        string $theme = 'light',
        ?int $employeeId = null,
        ?int $departmentId = null,
        ?int $id = null,
        ?string $email = null
    ): self {
        return new self($id, $username, $password, $role, $theme, $employeeId, $departmentId, $email);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function password(): ?string
    {
        return $this->password;
    }

    public function role(): UserRole
    {
        return $this->role;
    }

    public function theme(): string
    {
        return $this->theme;
    }

    public function employeeId(): ?int
    {
        return $this->employeeId;
    }

    public function departmentId(): ?int
    {
        return $this->departmentId;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function update(
        string $username,
        UserRole $role,
        ?int $departmentId,
        ?int $employeeId
    ): self {
        return new self(
            $this->id,
            $username,
            $this->password,
            $role,
            $this->theme,
            $employeeId,
            $departmentId
        );
    }
}
