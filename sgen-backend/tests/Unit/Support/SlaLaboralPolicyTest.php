<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Carbon\CarbonImmutable;
use Modules\Support\Domain\Enums\TicketPriority;
use Modules\Support\Domain\Services\SlaPolicy;
use PHPUnit\Framework\TestCase;

/**
 * SLA laboral (módulo 09): los fines de semana, los feriados y las horas
 * fuera del turno (L-V 08:00-18:00) NO cuentan para los plazos.
 */
final class SlaLaboralPolicyTest extends TestCase
{
    private function policy(array $feriados = []): SlaPolicy
    {
        return new SlaPolicy(
            ['critica' => 4, 'alta' => 8, 'media' => 24, 'baja' => 48],
            [1, 2, 3, 4, 5],
            '08:00',
            '18:00',
            $feriados
        );
    }

    public function test_crítico_creado_viernes_tarde_vence_el_lunes_laborable(): void
    {
        // Viernes 26-Sep-2026 (laborable) 17:00 + 4h críticas.
        $inicio = CarbonImmutable::parse('2026-09-25 17:00:00');
        $fin = $this->policy()->dueDateFor(TicketPriority::CRITICA, $inicio);

        // 1h del viernes (17:00→18:00), resto 3h el lunes (08:00→11:00).
        $this->assertSame(1, (int) $fin->dayOfWeekIso, 'Debe caer en lunes.');
        $this->assertSame('11:00', $fin->format('H:i'));
    }

    public function test_un_feriado_no_cuenta_como_laborable(): void
    {
        // Jueves 24-Dic-2026 16:00, 8h alta. En turno: 16→18 solo 2h.
        // Viernes 25 es feriado; sábado/domingo no son laborables; lunes 28
        // consume las 6h restantes (08:00→14:00). Así se saltan los feriados.
        $inicio = CarbonImmutable::parse('2026-12-24 16:00:00');
        $fin = $this->policy(['2026-12-25'])->dueDateFor(TicketPriority::ALTA, $inicio);

        $this->assertSame(1, (int) $fin->dayOfWeekIso, 'Debe caer en el lunes laborable posterior.');
        $this->assertSame('2026-12-28', $fin->format('Y-m-d'));
        $this->assertSame('14:00', $fin->format('H:i'));
    }

    public function test_fin_de_semana_no_resta_nada_a_la_resolucion(): void
    {
        // Sábado 2026-09-26 12:00: el ticket crítico avanza al lunes siguiente.
        $inicio = CarbonImmutable::parse('2026-09-26 12:00:00');
        $fin = $this->policy()->dueDateFor(TicketPriority::CRITICA, $inicio);

        $this->assertSame(1, (int) $fin->dayOfWeekIso);
        $this->assertSame('12:00', $fin->format('H:i')); // lunes 08:00 + 4h → 12:00
    }
}
