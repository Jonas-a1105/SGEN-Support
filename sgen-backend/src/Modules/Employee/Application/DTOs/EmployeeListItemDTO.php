<?php

declare(strict_types=1);

namespace Modules\Employee\Application\DTOs;

final class EmployeeListItemDTO
{
    public function __construct(
        public readonly int $numericId,
        public readonly string $id,
        public readonly string $fullName,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $position,
        public readonly string $dept,
        public readonly ?int $departmentId,
        public readonly string $email,
        public readonly string $idDoc,
        public readonly ?string $userAccount,
        public readonly string $initials,
        public readonly string $tint,
        public readonly string $role,
    ) {
    }

    public function toArray(): array
    {
        return [
            'numericId' => $this->numericId,
            'id' => $this->id,
            'fullName' => $this->fullName,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'position' => $this->position,
            'dept' => $this->dept,
            'departmentId' => $this->departmentId,
            'email' => $this->email,
            'idDoc' => $this->idDoc,
            'userAccount' => $this->userAccount,
            'initials' => $this->initials,
            'tint' => $this->tint,
            'role' => $this->role,
        ];
    }
}
