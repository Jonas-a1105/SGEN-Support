<?php

declare(strict_types=1);

namespace Modules\Equipment\Domain\Enums;

enum EquipmentStatus: string
{
    case NUEVO = 'nuevo';
    case USADO = 'usado';
    case EN_USO = 'en_uso';
    case FUERA_DE_SERVICIO = 'fuera_de_servicio';
    case EN_REPARACION = 'en_reparacion';
    case DISPONIBLE = 'disponible';
    case EN_RESERVA = 'en_reserva';

    public function label(): string
    {
        return match ($this) {
            self::NUEVO => 'Nuevo',
            self::USADO => 'Usado',
            self::EN_USO => 'En Uso',
            self::FUERA_DE_SERVICIO => 'Baja',
            self::EN_REPARACION => 'Reparación',
            self::DISPONIBLE => 'Disponible',
            self::EN_RESERVA => 'En Reserva',
        };
    }

    public static function fromLabel(string $label): self
    {
        return match (mb_strtolower(trim($label))) {
            'disponible' => self::DISPONIBLE,
            'en uso', 'en_uso' => self::EN_USO,
            'reparación', 'reparacion', 'en reparacion', 'en_reparacion' => self::EN_REPARACION,
            'baja', 'fuera de servicio', 'fuera_de_servicio' => self::FUERA_DE_SERVICIO,
            'nuevo' => self::NUEVO,
            'usado' => self::USADO,
            'en reserva', 'en_reserva' => self::EN_RESERVA,
            default => self::DISPONIBLE,
        };
    }
}
