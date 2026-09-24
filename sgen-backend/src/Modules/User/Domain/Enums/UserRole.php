<?php

declare(strict_types=1);

namespace Modules\User\Domain\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case TECNICO = 'tecnico';
    case CONSULTOR = 'consultor';
    case OPERADOR = 'operador';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::TECNICO => 'Técnico',
            self::CONSULTOR => 'Consultor',
            self::OPERADOR => 'Operador',
        };
    }

    public static function tryFromString(?string $val): self
    {
        if ($val === null) {
            return self::CONSULTOR;
        }

        $normalized = strtolower(trim($val));

        return match ($normalized) {
            'admin', 'administrador' => self::ADMIN,
            'tecnico', 'técnico' => self::TECNICO,
            'operador' => self::OPERADOR,
            default => self::CONSULTOR,
        };
    }
}
