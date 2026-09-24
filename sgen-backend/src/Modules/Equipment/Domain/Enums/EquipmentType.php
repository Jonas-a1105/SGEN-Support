<?php

declare(strict_types=1);

namespace Modules\Equipment\Domain\Enums;

enum EquipmentType: string
{
    case COMPUTADORA = 'Computadora';
    case IMPRESORA = 'Impresora';
    case TELEFONO = 'TELEFONO';
    case CAMARA = 'CAMARA';
    case SERVIDOR = 'Servidor';
    case OTRO = 'Otro';

    public function normalized(): string
    {
        return match ($this) {
            self::COMPUTADORA => 'Computadora',
            self::IMPRESORA => 'Impresora',
            self::TELEFONO => 'Teléfono',
            self::CAMARA => 'Cámara',
            self::SERVIDOR => 'Servidor',
            self::OTRO => 'Otro',
        };
    }

    public static function fromString(string $type): self
    {
        return match (mb_strtoupper(trim($type))) {
            'COMPUTADORA', 'PC', 'LAPTOP', 'DESKTOP' => self::COMPUTADORA,
            'IMPRESORA', 'PRINTER' => self::IMPRESORA,
            'TELEFONO', 'TELÉFONO', 'PHONE' => self::TELEFONO,
            'CAMARA', 'CÁMARA' => self::CAMARA,
            'SERVIDOR', 'SERVER' => self::SERVIDOR,
            default => self::OTRO,
        };
    }
}
