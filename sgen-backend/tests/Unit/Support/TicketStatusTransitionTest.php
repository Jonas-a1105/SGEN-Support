<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Modules\Support\Domain\Enums\TicketStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Cobertura combinatoria exhaustiva de la máquina de estados: las 25
 * transiciones posibles (5 estados × 5 estados) probadas una a una.
 * Si alguien edita la matriz, este test documenta y exige la intención.
 */
final class TicketStatusTransitionTest extends TestCase
{
    private const ALLOWED = [
        // [origen, destino, ¿permitida?]
        ['pendiente', 'pendiente', true],
        ['pendiente', 'en_proceso', true],
        ['pendiente', 'en_espera', true],
        ['pendiente', 'resuelto', true],
        ['pendiente', 'cerrado', false],

        ['en_proceso', 'pendiente', true],
        ['en_proceso', 'en_proceso', true],
        ['en_proceso', 'en_espera', true],
        ['en_proceso', 'resuelto', true],
        ['en_proceso', 'cerrado', false],

        ['en_espera', 'pendiente', false],
        ['en_espera', 'en_proceso', true],
        ['en_espera', 'en_espera', true],
        ['en_espera', 'resuelto', true],
        ['en_espera', 'cerrado', false],

        // Reapertura formal solo a en_proceso; cierre definitivo a cerrado.
        ['resuelto', 'pendiente', false],
        ['resuelto', 'en_proceso', true],
        ['resuelto', 'en_espera', false],
        ['resuelto', 'resuelto', true],
        ['resuelto', 'cerrado', true],

        // Estado terminal: inmutable.
        ['cerrado', 'pendiente', false],
        ['cerrado', 'en_proceso', false],
        ['cerrado', 'en_espera', false],
        ['cerrado', 'resuelto', false],
        ['cerrado', 'cerrado', true],
    ];

    #[DataProvider('transiciones')]
    public function test_transicion(string $origen, string $destino, bool $esperado): void
    {
        $this->assertSame(
            $esperado,
            TicketStatus::from($origen)->canTransitionTo(TicketStatus::from($destino)),
            "Regla FSM: {$origen} → {$destino}"
        );
    }

    /**
     * @return iterable<string, array{string, string, bool}>
     */
    public static function transiciones(): iterable
    {
        foreach (self::ALLOWED as [$origen, $destino, $esperado]) {
            yield "{$origen} → {$destino}" => [$origen, $destino, $esperado];
        }
    }
}
