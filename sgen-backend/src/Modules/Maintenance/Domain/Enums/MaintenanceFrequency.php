<?php

declare(strict_types=1);

namespace Modules\Maintenance\Domain\Enums;

enum MaintenanceFrequency: string
{
    case UNICA = 'unica';
    case MENSUAL = 'mensual';
    case TRIMESTRAL = 'trimestral';
    case SEMESTRAL = 'semestral';
    case ANUAL = 'anual';

    public function label(): string
    {
        return match($this) {
            self::UNICA => 'Única vez',
            self::MENSUAL => 'Mensual',
            self::TRIMESTRAL => 'Trimestral',
            self::SEMESTRAL => 'Semestral',
            self::ANUAL => 'Anual',
        };
    }

    public function daysInterval(): int
    {
        return match($this) {
            self::UNICA => 0,
            self::MENSUAL => 30,
            self::TRIMESTRAL => 90,
            self::SEMESTRAL => 180,
            self::ANUAL => 365,
        };
    }

    public function monthsInterval(): int
    {
        return match($this) {
            self::UNICA => 0,
            self::MENSUAL => 1,
            self::TRIMESTRAL => 3,
            self::SEMESTRAL => 6,
            self::ANUAL => 12,
        };
    }

    public function isRecurring(): bool
    {
        return $this !== self::UNICA;
    }
}
