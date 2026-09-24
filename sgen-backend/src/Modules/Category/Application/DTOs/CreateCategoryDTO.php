<?php

declare(strict_types=1);

namespace Modules\Category\Application\DTOs;

final readonly class CreateCategoryDTO
{
    public function __construct(
        public string $nombre,
        public ?string $descripcion,
        public string $icono,
        public string $color,
        public bool $activo = true
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nombre: (string) ($data['nombre'] ?? ''),
            descripcion: isset($data['descripcion']) ? (string) $data['descripcion'] : null,
            icono: (string) ($data['icono'] ?? 'hardware'),
            color: (string) ($data['color'] ?? '#0d6efd'),
            activo: (bool) ($data['activo'] ?? true)
        );
    }
}
