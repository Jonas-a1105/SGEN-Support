<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Enums;

enum TicketStatus: string
{
    case PENDIENTE = 'pendiente';
    case EN_PROCESO = 'en_proceso';
    case EN_ESPERA = 'en_espera';
    case RESUELTO = 'resuelto';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::EN_PROCESO => 'En proceso',
            self::EN_ESPERA => 'En espera',
            self::RESUELTO => 'Resuelto',
        };
    }

    public function uiVariant(?TicketPriority $priority = null): string
    {
        if ($this === self::PENDIENTE && $priority?->isCritical()) {
            return 'critical';
        }

        return match ($this) {
            self::PENDIENTE => 'pending',
            self::EN_PROCESO => 'process',
            self::EN_ESPERA => 'waiting',
            self::RESUELTO => 'resolved',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return match ($this) {
            self::PENDIENTE => true, // Puede pasar a en_proceso, en_espera o resuelto
            self::EN_PROCESO => in_array($target, [self::EN_ESPERA, self::RESUELTO, self::PENDIENTE], true),
            self::EN_ESPERA => in_array($target, [self::EN_PROCESO, self::RESUELTO, self::PENDIENTE], true),
            self::RESUELTO => false, // Un ticket resuelto no puede reabrirse directamente
        };
    }

    public function isPaused(): bool
    {
        return $this === self::EN_ESPERA;
    }

    public static function tryFromString(?string $value): self
    {
        if ($value === null) {
            return self::PENDIENTE;
        }

        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'en_proceso', 'proceso', 'process' => self::EN_PROCESO,
            'en_espera', 'espera', 'waiting' => self::EN_ESPERA,
            'resuelto', 'resolved' => self::RESUELTO,
            default => self::PENDIENTE,
        };
    }
}
