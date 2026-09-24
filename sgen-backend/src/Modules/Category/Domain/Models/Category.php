<?php

declare(strict_types=1);

namespace Modules\Category\Domain\Models;

use InvalidArgumentException;

final class Category
{
    private function __construct(
        private ?int $id,
        private string $name,
        private ?string $description,
        private string $icon,
        private string $color,
        private bool $active
    ) {
        $trimmedName = trim($this->name);
        if ($trimmedName === '') {
            throw new InvalidArgumentException('El nombre de la categoría no puede estar vacío.');
        }

        $trimmedColor = trim($this->color);
        if ($trimmedColor === '' || !preg_match('/^#[a-fA-F0-9]{3,8}$/', $trimmedColor)) {
            $this->color = '#0d6efd';
        }
    }

    public static function create(
        string $name,
        ?string $description = null,
        string $icon = 'hardware',
        string $color = '#0d6efd',
        bool $active = true,
        ?int $id = null
    ): self {
        return new self($id, $name, $description, $icon, $color, $active);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function icon(): string
    {
        return $this->icon;
    }

    public function color(): string
    {
        return $this->color;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function update(
        string $name,
        ?string $description,
        string $icon,
        string $color
    ): self {
        return new self($this->id, $name, $description, $icon, $color, $this->active);
    }

    public function deactivate(): self
    {
        return new self($this->id, $this->name, $this->description, $this->icon, $this->color, false);
    }

    public function activate(): self
    {
        return new self($this->id, $this->name, $this->description, $this->icon, $this->color, true);
    }
}
