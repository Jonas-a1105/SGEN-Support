<?php

declare(strict_types=1);

namespace Modules\Category\Application\DTOs;

final readonly class CategoryListItemDTO
{
    public function __construct(
        public int $id,
        public string $nombre,
        public ?string $descripcion,
        public string $icono,
        public string $color,
        public bool $activo,
        public int $totalTickets = 0
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'icono' => $this->icono,
            'color' => $this->color,
            'activo' => $this->activo,
            'total_tickets' => $this->totalTickets,
        ];
    }
}
