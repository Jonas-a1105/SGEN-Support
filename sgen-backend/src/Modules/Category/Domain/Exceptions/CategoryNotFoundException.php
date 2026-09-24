<?php

declare(strict_types=1);

namespace Modules\Category\Domain\Exceptions;

use RuntimeException;

final class CategoryNotFoundException extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("La categoría con ID {$id} no fue encontrada.");
    }
}
