<?php

declare(strict_types=1);

namespace Modules\Department\Application\DTOs;

final class UpdateDepartmentDTO
{
    public function __construct(
        public readonly ?string $nombre = null,
        public readonly ?string $ubicacion = null,
        public readonly ?string $jefeAreaNombre = null,
        public readonly ?int $jefeAreaId = null,
        public readonly ?string $descripcion = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            nombre: isset($data['nombre']) ? (string) $data['nombre'] : (isset($data['name']) ? (string) $data['name'] : null),
            ubicacion: array_key_exists('ubicacion', $data) ? ($data['ubicacion'] !== null ? (string) $data['ubicacion'] : null) : (array_key_exists('location', $data) ? ($data['location'] !== null ? (string) $data['location'] : null) : null),
            jefeAreaNombre: array_key_exists('jefe_area_nombre', $data) ? ($data['jefe_area_nombre'] !== null ? (string) $data['jefe_area_nombre'] : null) : (array_key_exists('manager', $data) ? ($data['manager'] !== null ? (string) $data['manager'] : null) : null),
            jefeAreaId: array_key_exists('jefe_area_id', $data) ? ($data['jefe_area_id'] !== null ? (int) $data['jefe_area_id'] : null) : (array_key_exists('managerId', $data) ? ($data['managerId'] !== null ? (int) $data['managerId'] : null) : null),
            descripcion: array_key_exists('descripcion', $data) ? ($data['descripcion'] !== null ? (string) $data['descripcion'] : null) : (array_key_exists('desc', $data) ? ($data['desc'] !== null ? (string) $data['desc'] : null) : null),
        );
    }
}
