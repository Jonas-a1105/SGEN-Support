<?php

declare(strict_types=1);

namespace Tests\Feature\Notification;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Notification\Application\Services\EmailThrottler;
use Tests\TestCase;

/**
 * #45: un canal de correo no spam — cooldown por (tipo, destinatario);
 * el seguimiento queda registrado en `correos_enviados`.
 */
final class EmailThrottlerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_el_primer_correo_pasa_y_el_siguiente_inmediato_se_suprime(): void
    {
        $usuario = (int) DB::table('usuarios')->insertGetId([
            'username' => 'throttle_'.uniqid(),
            'password' => \Illuminate\Support\Facades\Hash::make('secret'),
            'rol' => 'tecnico',
            'tema' => 'light',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Primera salida: permitida y registrada.
        $this->assertTrue(EmailThrottler::permitir($usuario, null, 'ticket_asignado', 'Ticket asignado'));

        // Segunda salida inmediata del mismo tipo: bloqueada.
        $this->assertFalse(EmailThrottler::permitir($usuario, null, 'ticket_asignado', 'Ticket asignado'));

        // Otro tipo de notificación NO se bloquea entre sí.
        $this->assertTrue(EmailThrottler::permitir($usuario, null, 'ticket_resuelto', 'Ticket resuelto'));
    }

    public function test_tras_el_cooldown_vuelve_a_fluir(): void
    {
        $usuario = (int) DB::table('usuarios')->insertGetId([
            'username' => 'throttle_exp_'.uniqid(),
            'password' => \Illuminate\Support\Facades\Hash::make('secret'),
            'rol' => 'tecnico',
            'tema' => 'light',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        EmailThrottler::permitir($usuario, null, 'sla_vencido');

        // Forzar el registro a "hace una hora": el cooldown de 30 min quedó atrás.
        DB::table('correos_enviados')
            ->where('usuario_id', $usuario)
            ->where('tipo', 'sla_vencido')
            ->update(['created_at' => now()->subHours(2)]);

        $this->assertTrue(EmailThrottler::permitir($usuario, null, 'sla_vencido'));
    }
}
