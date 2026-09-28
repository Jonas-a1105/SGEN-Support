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
    case PRESTADO = 'prestado';
    case DE_BAJA = 'de_baja';
    case PERDIDO = 'perdido';

    public function label(): string
    {
        return match ($this) {
            self::NUEVO => 'Nuevo',
            self::USADO => 'Usado',
            self::EN_USO => 'En Uso',
            self::FUERA_DE_SERVICIO => 'Fuera de servicio',
            self::EN_REPARACION => 'Reparación',
            self::DISPONIBLE => 'Disponible',
            self::EN_RESERVA => 'En Reserva',
            self::PRESTADO => 'Prestado',
            self::DE_BAJA => 'De baja',
            self::PERDIDO => 'Perdido',
        };
    }

    /**
     * Regla #30: un equipo dado de baja jamás recibe tickets nuevos;
     * "fuera_de_servicio" solo advierte (puede volver a operar).
     */
    public function aceptaTicketsNuevos(): bool
    {
        return $this !== self::DE_BAJA;
    }

    public static function fromLabel(string $label): self
    {
        return self::tryFromLabel($label) ?? self::DISPONIBLE;
    }

    /**
     * Normaliza etiquetas legadas sin inventar un estado: null cuando la
     * etiqueta no corresponde a ningún estado real.
     */
    public static function tryFromLabel(string $label): ?self
    {
        return match (mb_strtolower(trim($label))) {
            'disponible' => self::DISPONIBLE,
            'en uso', 'en_uso' => self::EN_USO,
            'reparación', 'reparacion', 'en reparacion', 'en_reparacion' => self::EN_REPARACION,
            'fuera de servicio', 'fuera_de_servicio' => self::FUERA_DE_SERVICIO,
            'baja', 'de baja', 'de_baja', 'dado de baja' => self::DE_BAJA,
            'prestado' => self::PRESTADO,
            'perdido' => self::PERDIDO,
            'nuevo' => self::NUEVO,
            'usado' => self::USADO,
            'en reserva', 'en_reserva' => self::EN_RESERVA,
            default => null,
        };
    }
}
