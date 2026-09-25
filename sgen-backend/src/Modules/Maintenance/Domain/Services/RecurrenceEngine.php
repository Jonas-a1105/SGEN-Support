<?php

declare(strict_types=1);

namespace Modules\Maintenance\Domain\Services;

use Carbon\Carbon;

/**
 * Motor de recurrencia del CMMS:
 * calcula la próxima fecha de una orden según su frecuencia y
 * materializa la siguiente orden de la serie (genealogía serie_padre_id).
 */
final class RecurrenceEngine
{
    public function nextDate(string $frecuencia, ?Carbon $baseDate = null): ?Carbon
    {
        $base = $baseDate ?? Carbon::now();

        $calc = match ($frecuencia) {
            'mensual' => $base->copy()->addMonth(),
            'trimestral' => $base->copy()->addMonths(3),
            'semestral' => $base->copy()->addMonths(6),
            'anual' => $base->copy()->addYear(),
            'unica' => null,
            default => null,
        };

        if ($calc === null) {
            return null;
        }

        if ($calc->isPast()) {
            $now = Carbon::now();

            return match ($frecuencia) {
                'mensual' => $now->copy()->addMonth(),
                'trimestral' => $now->copy()->addMonths(3),
                'semestral' => $now->copy()->addMonths(6),
                'anual' => $now->copy()->addYear(),
                default => $now->copy()->addMonth(),
            };
        }

        return $calc;
    }

    /**
     * Determina si debe materializarse la siguiente orden de una serie.
     * Regla del documento maestro: nadie debe crear manualmente las
     * recurrentes; el completar() las genera, y un job de seguridad
     * regenera si faltan 7 días para la fecha y no existe la siguiente.
     */
    public function shouldMaterializeNext(string $frecuencia, ?string $proximaFecha): bool
    {
        if ($frecuencia === 'unica' || $proximaFecha === null) {
            return false;
        }

        $proxima = Carbon::parse($proximaFecha);

        return $proxima->lessThanOrEqualTo(Carbon::now()->addDays(7));
    }
}
