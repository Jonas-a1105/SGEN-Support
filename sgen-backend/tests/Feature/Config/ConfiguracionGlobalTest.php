<?php

declare(strict_types=1);

namespace Tests\Feature\Config;

use App\Support\Config\ConfiguracionGlobal;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Configuración global persistida: lectura tipada, valores por defecto
 * seguros e invalidación de caché al actualizar una clave.
 */
final class ConfiguracionGlobalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_valor_por_defecto_cuando_la_clave_no_existe(): void
    {
        ConfiguracionGlobal::establecer('test.clave_inexistente', null);

        $this->assertSame(42, ConfiguracionGlobal::entero('test.no_existe_en_absoluto', 42));
    }

    public function test_lectura_y_actualizacion_tipadas(): void
    {
        ConfiguracionGlobal::establecer('test.limite', '7');

        $this->assertSame(7, ConfiguracionGlobal::entero('test.limite', 1));

        ConfiguracionGlobal::establecer('test.limite', '15');

        // El caché se invalida al actualizar: se ve el valor nuevo de inmediato.
        $this->assertSame(15, ConfiguracionGlobal::entero('test.limite', 1));

        ConfiguracionGlobal::establecer('test.interruptor', 'true');
        $this->assertTrue(ConfiguracionGlobal::booleano('test.interruptor', false));

        ConfiguracionGlobal::establecer('test.interruptor', 'off');
        $this->assertFalse(ConfiguracionGlobal::booleano('test.interruptor', true));
    }

    public function test_las_claves_sembradas_existen(): void
    {
        $this->assertDatabaseHas('configuracion_global', ['clave' => 'seguridad.login_intentos_max']);
        $this->assertDatabaseHas('configuracion_global', ['clave' => 'tickets.autocierre_dias']);
    }
}
