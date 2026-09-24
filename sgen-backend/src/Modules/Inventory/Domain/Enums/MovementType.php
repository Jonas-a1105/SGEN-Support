<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Enums;

enum MovementType: string
{
    case ENTRADA = 'ENTRADA';
    case SALIDA = 'SALIDA';
    case AJUSTE = 'AJUSTE';
    case BAJA = 'BAJA';
    case CONSUMO = 'CONSUMO';
    case TRANSFERENCIA = 'TRANSFERENCIA';

    public function isAddition(): bool
    {
        return $this === self::ENTRADA;
    }

    public function isReduction(): bool
    {
        return in_array($this, [self::SALIDA, self::BAJA, self::CONSUMO], true);
    }
}
