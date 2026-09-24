<?php

declare(strict_types=1);

namespace Modules\Maintenance\Domain\Enums;

enum MaintenanceStatus: string
{
    case PENDIENTE = 'pendiente';
    case EN_PROCESO = 'en_proceso';
    case COMPLETADO = 'completado';
    case POSPUESTO = 'pospuesto';
    case CANCELADO = 'cancelado';

    public function label(): string
    {
        return match($this) {
            self::PENDIENTE => 'Pendiente',
            self::EN_PROCESO => 'En Proceso',
            self::COMPLETADO => 'Completado',
            self::POSPUESTO => 'Pospuesto',
            self::CANCELADO => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDIENTE => 'orange',
            self::EN_PROCESO => 'blue',
            self::COMPLETADO => 'green',
            self::POSPUESTO => 'yellow',
            self::CANCELADO => 'red',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDIENTE, self::EN_PROCESO], true);
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETADO;
    }
}
