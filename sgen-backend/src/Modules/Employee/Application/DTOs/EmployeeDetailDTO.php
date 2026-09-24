<?php

declare(strict_types=1);

namespace Modules\Employee\Application\DTOs;

final class EmployeeDetailDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $formattedId,
        public readonly string $nombre,
        public readonly string $apellido,
        public readonly string $fullName,
        public readonly string $email,
        public readonly ?string $cedula,
        public readonly ?string $cargo,
        public readonly ?int $departamentoId,
        public readonly ?string $departamentoNombre,
        public readonly string $rol,
        public readonly ?int $usuarioId,
        public readonly ?string $username,
        public readonly string $initials,
        public readonly string $tint,
        public readonly ?string $telefono = null,
        public readonly array $equipos = [],
        public readonly array $tickets = [],
        public readonly array $departamentos = [],
        public readonly int $equiposCount = 0,
        public readonly int $ticketsCount = 0,
        public readonly int $resolvedTicketsCount = 0,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'formattedId' => $this->formattedId,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'fullName' => $this->fullName,
            'email' => $this->email,
            'cedula' => $this->cedula,
            'cargo' => $this->cargo,
            'telefono' => $this->telefono,
            'departamentoId' => $this->departamentoId,
            'departamentoNombre' => $this->departamentoNombre,
            'rol' => $this->rol,
            'usuarioId' => $this->usuarioId,
            'username' => $this->username,
            'initials' => $this->initials,
            'tint' => $this->tint,
            'equipos' => $this->equipos,
            'tickets' => $this->tickets,
            'departamentos' => $this->departamentos,
            'equiposCount' => $this->equiposCount,
            'ticketsCount' => $this->ticketsCount,
            'resolvedTicketsCount' => $this->resolvedTicketsCount,
        ];
    }
}
