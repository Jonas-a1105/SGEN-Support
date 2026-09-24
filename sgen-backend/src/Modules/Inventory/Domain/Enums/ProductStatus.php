<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Enums;

enum ProductStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case DISCONTINUED = 'DISCONTINUED';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Activo',
            self::INACTIVE => 'Inactivo',
            self::DISCONTINUED => 'Descontinuado',
        };
    }
}
