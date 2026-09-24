<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Enums;

enum TicketPriority: string
{
    case BAJA = 'baja';
    case MEDIA = 'media';
    case ALTA = 'alta';
    case CRITICA = 'critica';

    public function label(): string
    {
        return match ($this) {
            self::BAJA => 'Baja',
            self::MEDIA => 'Media',
            self::ALTA => 'Alta',
            self::CRITICA => 'Crítica',
        };
    }

    public function isCritical(): bool
    {
        return $this === self::CRITICA;
    }

    public function slaHours(): int
    {
        return match ($this) {
            self::CRITICA => 4,
            self::ALTA => 8,
            self::MEDIA => 24,
            self::BAJA => 48,
        };
    }

    public static function tryFromString(?string $value): self
    {
        if ($value === null) {
            return self::MEDIA;
        }

        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'critica', 'crítica', 'critical' => self::CRITICA,
            'alta', 'high' => self::ALTA,
            'baja', 'low' => self::BAJA,
            default => self::MEDIA,
        };
    }
}
