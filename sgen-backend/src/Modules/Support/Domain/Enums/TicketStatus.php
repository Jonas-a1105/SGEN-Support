<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Enums;

use InvalidArgumentException;

enum TicketStatus: string
{
    case PENDIENTE = 'pendiente';
    case EN_PROCESO = 'en_proceso';
    case EN_ESPERA = 'en_espera';
    case RESUELTO = 'resuelto';
    case CERRADO = 'cerrado';
    case CANCELADO = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::EN_PROCESO => 'En proceso',
            self::EN_ESPERA => 'En espera',
            self::RESUELTO => 'Resuelto',
            self::CERRADO => 'Cerrado',
            self::CANCELADO => 'Cancelado',
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
            self::CERRADO => 'closed',
            self::CANCELADO => 'cancelled',
        };
    }

    /**
     * Máquina de estados finita — única fuente de verdad del ciclo de vida.
     *
     *   PENDIENTE  → EN_PROCESO | EN_ESPERA | RESUELTO | CANCELADO
     *   EN_PROCESO → EN_ESPERA | RESUELTO | PENDIENTE | CANCELADO
     *   EN_ESPERA  → EN_PROCESO | RESUELTO | CANCELADO
     *   RESUELTO   → CERRADO (autocierre/cierre manual) | EN_PROCESO (reapertura justificada)
     *   CERRADO    → ∅ (estado terminal, inmutable)
     *   CANCELADO  → ∅ (estado terminal: un ticket anulado jamás revive)
     */
    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return match ($this) {
            self::PENDIENTE => in_array($target, [self::EN_PROCESO, self::EN_ESPERA, self::RESUELTO, self::CANCELADO], true),
            self::EN_PROCESO => in_array($target, [self::EN_ESPERA, self::RESUELTO, self::PENDIENTE, self::CANCELADO], true),
            self::EN_ESPERA => in_array($target, [self::EN_PROCESO, self::RESUELTO, self::CANCELADO], true),
            self::RESUELTO => in_array($target, [self::CERRADO, self::EN_PROCESO], true),
            self::CERRADO => false,
            self::CANCELADO => false,
        };
    }

    public function isPaused(): bool
    {
        return $this === self::EN_ESPERA;
    }

    /**
     * Estados finales del ciclo de vida: excluidos del motor de SLA,
     * del autocierre y de las colas de trabajo activas.
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::RESUELTO, self::CERRADO, self::CANCELADO], true);
    }

    /**
     * Valores de los estados finales, para whereNotIn/whereIn sin strings mágicos.
     *
     * @return list<string>
     */
    public static function finalValues(): array
    {
        return array_values(array_map(
            static fn (self $status): string => $status->value,
            array_filter(self::cases(), static fn (self $status): bool => $status->isFinal())
        ));
    }

    /**
     * Hidratación estricta: un valor desconocido es un error de datos,
     * no un ticket pendiente silencioso.
     */
    public static function tryFromString(?string $value): self
    {
        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            '', 'pendiente', 'pending' => self::PENDIENTE,
            'en_proceso', 'proceso', 'process' => self::EN_PROCESO,
            'en_espera', 'espera', 'waiting' => self::EN_ESPERA,
            'resuelto', 'resolved' => self::RESUELTO,
            'cerrado', 'closed' => self::CERRADO,
            'cancelado', 'cancelled', 'anulado' => self::CANCELADO,
            default => throw new InvalidArgumentException(
                "Estado de ticket desconocido en persistencia: [{$value}]."
            ),
        };
    }
}
