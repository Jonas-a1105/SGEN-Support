<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use InvalidArgumentException;
use Modules\Support\Domain\Enums\TicketStatus;
use PHPUnit\Framework\TestCase;

final class TicketStatusTest extends TestCase
{
    public function test_cerrado_es_estado_terminal(): void
    {
        foreach ([TicketStatus::PENDIENTE, TicketStatus::EN_PROCESO, TicketStatus::EN_ESPERA, TicketStatus::RESUELTO] as $target) {
            $this->assertFalse(
                TicketStatus::CERRADO->canTransitionTo($target),
                "Un ticket CERRADO no debe permitir transición a {$target->value}"
            );
        }
    }

    public function test_resuelto_solo_puede_cerrarse_o_reabrirse(): void
    {
        $this->assertTrue(TicketStatus::RESUELTO->canTransitionTo(TicketStatus::CERRADO));
        $this->assertTrue(TicketStatus::RESUELTO->canTransitionTo(TicketStatus::EN_PROCESO));
        $this->assertFalse(TicketStatus::RESUELTO->canTransitionTo(TicketStatus::PENDIENTE));
        $this->assertFalse(TicketStatus::RESUELTO->canTransitionTo(TicketStatus::EN_ESPERA));
    }

    public function test_pendiente_no_puede_saltarse_a_cerrado(): void
    {
        $this->assertTrue(TicketStatus::PENDIENTE->canTransitionTo(TicketStatus::EN_PROCESO));
        $this->assertTrue(TicketStatus::PENDIENTE->canTransitionTo(TicketStatus::EN_ESPERA));
        $this->assertTrue(TicketStatus::PENDIENTE->canTransitionTo(TicketStatus::RESUELTO));
        $this->assertFalse(TicketStatus::PENDIENTE->canTransitionTo(TicketStatus::CERRADO));
    }

    public function test_en_espera_no_puede_cerrarse_directamente(): void
    {
        $this->assertTrue(TicketStatus::EN_ESPERA->canTransitionTo(TicketStatus::EN_PROCESO));
        $this->assertTrue(TicketStatus::EN_ESPERA->canTransitionTo(TicketStatus::RESUELTO));
        $this->assertFalse(TicketStatus::EN_ESPERA->canTransitionTo(TicketStatus::CERRADO));
        $this->assertFalse(TicketStatus::EN_ESPERA->canTransitionTo(TicketStatus::PENDIENTE));
    }

    public function test_estados_finales_para_consultas(): void
    {
        $this->assertTrue(TicketStatus::RESUELTO->isFinal());
        $this->assertTrue(TicketStatus::CERRADO->isFinal());
        $this->assertFalse(TicketStatus::PENDIENTE->isFinal());
        $this->assertFalse(TicketStatus::EN_PROCESO->isFinal());
        $this->assertFalse(TicketStatus::EN_ESPERA->isFinal());

        $this->assertSame(['resuelto', 'cerrado'], TicketStatus::finalValues());
    }

    public function test_hidratacion_reconoce_cerrado(): void
    {
        $this->assertSame(TicketStatus::CERRADO, TicketStatus::tryFromString('cerrado'));
        $this->assertSame(TicketStatus::CERRADO, TicketStatus::tryFromString('closed'));
        $this->assertSame(TicketStatus::CERRADO, TicketStatus::tryFromString('  CERRADO '));
        $this->assertSame(TicketStatus::PENDIENTE, TicketStatus::tryFromString(null));
        $this->assertSame(TicketStatus::PENDIENTE, TicketStatus::tryFromString(''));
    }

    public function test_hidratacion_rechaza_estados_desconocidos(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/cancelado/');

        TicketStatus::tryFromString('cancelado');
    }

    public function test_variantes_y_etiquetas_del_nuevo_estado(): void
    {
        $this->assertSame('closed', TicketStatus::CERRADO->uiVariant());
        $this->assertSame('Cerrado', TicketStatus::CERRADO->label());
    }
}
