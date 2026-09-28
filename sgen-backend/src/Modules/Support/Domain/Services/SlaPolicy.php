<?php

declare(strict_types=1);

namespace Modules\Support\Domain\Services;

use Carbon\CarbonImmutable;
use Modules\Support\Domain\Enums\TicketPriority;

/**
 * Política de SLA del dominio de Soporte.
 * Calcula la fecha de vencimiento de resolución a partir de la prioridad.
 *
 * Las horas objetivo provienen de la configuración (config/sla.php),
 * con fallback a las horas del enum de dominio TicketPriority.
 */
final class SlaPolicy
{
    /**
     * @param  array<string, int>  $hoursByPriority  Mapa prioridad => horas de resolución.
     */
    /**
     * @param  array<string, int>  $hoursByPriority  Mapa prioridad => horas de resolución.
     * @param  array<int>  $diasLaborales  Días laborales (ISO: 1 lunes … 7 domingo).
     * @param  array<string>  $feriados  Fechas 'Y-m-d' que no cuentan como laborales.
     */
    public function __construct(
        private readonly array $hoursByPriority,
        private readonly array $diasLaborales = [1, 2, 3, 4, 5],
        private readonly string $horaInicio = '08:00',
        private readonly string $horaFin = '18:00',
        private readonly array $feriados = []
    ) {}

    public static function fromConfig(): self
    {
        /** @var array<string, int> $config */
        $config = config('sla.hours_by_priority', []);
        $operativo = (array) config('sla.operativo', []);

        return new self(
            $config,
            (array) ($operativo['dias_laborales'] ?? [1, 2, 3, 4, 5]),
            (string) ($operativo['hora_inicio'] ?? '08:00'),
            (string) ($operativo['hora_fin'] ?? '18:00')
        );
    }

    /**
     * Dominio puro: los feriados los inyecta la capa de aplicación.
     *
     * @param  array<string>  $feriados  Fechas 'Y-m-d' que no laboran.
     */
    public function withFeriados(array $feriados): self
    {
        return new self(
            $this->hoursByPriority,
            $this->diasLaborales,
            $this->horaInicio,
            $this->horaFin,
            $feriados
        );
    }

    public function hoursFor(TicketPriority $priority): int
    {
        return $this->hoursByPriority[$priority->value] ?? $priority->slaHours();
    }

    /**
     * Fecha de vencimiento en TIEMPO LABORAL (módulo 09): los fines de
     * semana, los feriados y las horas fuera del turno no cuentan.
     * Una incidencia creada el viernes 17:00 con 4h de SLA crítico vence
     * el lunes siguiente (no el viernes tarde-noche noche).
     */
    public function dueDateFor(TicketPriority $priority, ?CarbonImmutable $from = null): CarbonImmutable
    {
        $restantes = $this->hoursFor($priority) * 60; // minutos
        $cursor = $from ?? CarbonImmutable::now();

        while ($restantes > 0) {
            $cursor = $this->avanzarAHorarioLaboral($cursor);

            [$inicioTurno, $finTurno] = $this->turnoDe($cursor);
            $minutosDisponibles = max(1, $cursor->diffInMinutes($finTurno));
            $consumir = min($restantes, $minutosDisponibles);

            $cursor = $cursor->addMinutes($consumir);
            $restantes -= $consumir;

            if ($restantes > 0) {
                $cursor = $this->siguienteDiaLaboral($cursor->startOfDay()->addDay());
            }
        }

        return $cursor;
    }

    /** Mueve el cursor al siguiente openslot de horario laboral. */
    private function avanzarAHorarioLaboral(CarbonImmutable $cursor): CarbonImmutable
    {
        while (true) {
            if (! $this->esDiaLaboral($cursor)) {
                $cursor = $this->siguienteDiaLaboral($cursor->startOfDay()->addDay());

                continue;
            }

            [$inicioTurno, $finTurno] = $this->turnoDe($cursor);

            if ($cursor->lessThan($inicioTurno)) {
                return $inicioTurno;
            }

            if ($cursor->greaterThanOrEqualTo($finTurno)) {
                $cursor = $this->siguienteDiaLaboral($cursor->startOfDay()->addDay());

                continue;
            }

            return $cursor;
        }
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private function turnoDe(CarbonImmutable $dia): array
    {
        [$hiH, $hiM] = array_map('intval', explode(':', $this->horaInicio));
        [$hfH, $hfM] = array_map('intval', explode(':', $this->horaFin));

        return [
            $dia->startOfDay()->addHours($hiH)->addMinutes($hiM),
            $dia->startOfDay()->addHours($hfH)->addMinutes($hfM),
        ];
    }

    private function esDiaLaboral(CarbonImmutable $dia): bool
    {
        return in_array($dia->dayOfWeekIso, $this->diasLaborales, true)
            && ! in_array($dia->format('Y-m-d'), $this->feriados, true);
    }

    private function siguienteDiaLaboral(CarbonImmutable $dia): CarbonImmutable
    {
        while (! $this->esDiaLaboral($dia)) {
            $dia = $dia->addDay();
        }

        return $dia;
    }

    public function extendDueDate(CarbonImmutable $currentDueDate, int $minutes): CarbonImmutable
    {
        if ($minutes <= 0) {
            return $currentDueDate;
        }

        return $currentDueDate->addMinutes($minutes);
    }

    /**
     * Extensión temporal POR HORARIO LABORAL (usa el mismo calendario del
     * módulo 09): pausar un viernes 17:50 no suma tiempo al fin de semana;
     * la computadora mueve el vencimiento al próximo bloque abierto.
     */
    public function extendDueDateBusiness(CarbonImmutable $currentDueDate, int $minutes): CarbonImmutable
    {
        if ($minutes <= 0) {
            return $currentDueDate;
        }

        $restantes = $minutes;
        $cursor = $currentDueDate;

        while ($restantes > 0) {
            $cursor = $this->avanzarAHorarioLaboral($cursor);

            [, $finTurno] = $this->turnoDe($cursor);
            $minutosDisponibles = max(1, $cursor->diffInMinutes($finTurno));
            $consumir = min($restantes, $minutosDisponibles);

            $cursor = $cursor->addMinutes($consumir);
            $restantes -= $consumir;

            if ($restantes > 0) {
                $cursor = $this->siguienteDiaLaboral($cursor->startOfDay()->addDay());
            }
        }

        return $cursor;
    }

    public function isOverdue(CarbonImmutable $dueDate, ?CarbonImmutable $reference = null): bool
    {
        $reference ??= CarbonImmutable::now();

        return $reference->greaterThan($dueDate);
    }
}
