<?php

declare(strict_types=1);

namespace Modules\Employee\Domain\Enums;

enum EmployeeRole: string
{
    case TECNICO = 'tecnico';
    case ADMINISTRADOR = 'administrador';
    case CONSULTOR = 'consultor';

    public function label(): string
    {
        return match ($this) {
            self::TECNICO => 'Técnico',
            self::ADMINISTRADOR => 'Administrador',
            self::CONSULTOR => 'Consultor',
        };
    }

    public static function fromString(string $val): self
    {
        return match (mb_strtolower(trim($val))) {
            'administrador', 'admin' => self::ADMINISTRADOR,
            'tecnico', 'técnico' => self::TECNICO,
            default => self::CONSULTOR,
        };
    }
}
