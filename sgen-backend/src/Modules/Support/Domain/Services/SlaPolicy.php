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
    public function __construct(
        private readonly array $hoursByPriority
    ) {}

    public static function fromConfig(): self
    {
        /** @var array<string, int> $config */
        $config = config('sla.hours_by_priority', []);

        return new self($config);
    }

    public function hoursFor(TicketPriority $priority): int
    {
        return $this->hoursByPriority[$priority->value] ?? $priority->slaHours();
    }

    public function dueDateFor(TicketPriority $priority, ?CarbonImmutable $from = null): CarbonImmutable
    {
        $from ??= CarbonImmutable::now();

        return $from->addHours($this->hoursFor($priority));
    }

    public function extendDueDate(CarbonImmutable $currentDueDate, int $minutes): CarbonImmutable
    {
        if ($minutes <= 0) {
            return $currentDueDate;
        }

        return $currentDueDate->addMinutes($minutes);
    }

    public function isOverdue(CarbonImmutable $dueDate, ?CarbonImmutable $reference = null): bool
    {
        $reference ??= CarbonImmutable::now();

        return $reference->greaterThan($dueDate);
    }
}
