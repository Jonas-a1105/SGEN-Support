<?php

declare(strict_types=1);

namespace Modules\Department\Application\DTOs;

final class CreateDepartmentDTO
{
    public function __construct(
        public readonly string $nombre,
        public readonly ?string $ubicacion = null,
        public readonly ?string $jefeAreaNombre = null,
        public readonly ?int $jefeAreaId = null,
        public readonly ?string $descripcion = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            nombre: (string) ($data['nombre'] ?? $data['name'] ?? ''),
            ubicacion: isset($data['ubicacion']) ? (string) $data['ubicacion'] : (isset($data['location']) ? (string) $data['location'] : null),
            jefeAreaNombre: isset($data['jefe_area_nombre']) ? (string) $data['jefe_area_nombre'] : (isset($data['manager']) ? (string) $data['manager'] : null),
            jefeAreaId: isset($data['jefe_area_id']) ? (int) $data['jefe_area_id'] : (isset($data['managerId']) ? (int) $data['managerId'] : null),
            descripcion: isset($data['descripcion']) ? (string) $data['descripcion'] : (isset($data['desc']) ? (string) $data['desc'] : null),
        );
    }
}
