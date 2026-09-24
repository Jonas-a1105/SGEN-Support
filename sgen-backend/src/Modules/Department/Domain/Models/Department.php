<?php

declare(strict_types=1);

namespace Modules\Department\Domain\Models;

use InvalidArgumentException;

final class Department
{
    public function __construct(
        private readonly ?int $id,
        private string $nombre,
        private ?string $ubicacion = null,
        private ?string $jefeAreaNombre = null,
        private ?int $jefeAreaId = null,
        private ?string $descripcion = null,
    ) {
        $this->ensureInvariants();
    }

    private function ensureInvariants(): void
    {
        if (trim($this->nombre) === '') {
            throw new InvalidArgumentException('El nombre del departamento no puede estar vacío.');
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

    public function ubicacion(): ?string
    {
        return $this->ubicacion;
    }

    public function jefeAreaNombre(): ?string
    {
        return $this->jefeAreaNombre;
    }

    public function jefeAreaId(): ?int
    {
        return $this->jefeAreaId;
    }

    public function descripcion(): ?string
    {
        return $this->descripcion;
    }

    public function assignManager(string $managerName, ?int $managerId = null): void
    {
        $this->jefeAreaNombre = $managerName;
        $this->jefeAreaId = $managerId;
    }

    public function clearManager(): void
    {
        $this->jefeAreaNombre = null;
        $this->jefeAreaId = null;
    }
}
