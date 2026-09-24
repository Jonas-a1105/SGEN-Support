<?php

declare(strict_types=1);

namespace Modules\Equipment\Domain\Exceptions;

use DomainException;

final class EquipmentNotFoundException extends DomainException
{
    public static function withId(int|string $id): self
    {
        return new self("El equipo con ID [{$id}] no fue encontrado en el sistema.");
    }
}
