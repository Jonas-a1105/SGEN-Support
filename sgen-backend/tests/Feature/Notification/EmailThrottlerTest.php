<?php

declare(strict_types=1);

namespace Tests\Feature\Notification;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Notification\Application\Services\EmailThrottler;
use Tests\TestCase;

/**
 * #45: un canal de correo no spam — cooldown por (tipo, destinatario);
 * el seguimiento queda registrado en `correos_enviados` SOLO tras un envío
 * aceptado (`canSend()` decide, `markSent()` registra).
 */
final class EmailThrottlerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_el_primer_correo_pasa_y_el_siguiente_inmediato_se_suprime(): void
    {
        $usuario = (int) DB::table('usuarios')->insertGetId([
            'username' => 'throttle_'.uniqid(),
            'password' => Hash::make('secret'),
            'rol' => 'tecnico',
            'tema' => 'light',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Primera salida: permitida y todavía sin registrar (el registro es post-envío).
        $this->assertTrue(EmailThrottler::canSend($usuario, null, 'ticket_asignado'));
        $this->assertDatabaseMissing('correos_enviados', [
            'usuario_id' => $usuario,
            'tipo' => 'ticket_asignado',
        ]);

        // Envío aceptado: recién ahí se consume la ventana.
        EmailThrottler::markSent($usuario, null, 'ticket_asignado', 'Ticket asignado');

        // Segunda salida inmediata del mismo tipo: bloqueada.
        $this->assertFalse(EmailThrottler::canSend($usuario, null, 'ticket_asignado'));

        // Otro tipo de notificación NO se bloquea entre sí.
        $this->assertTrue(EmailThrottler::canSend($usuario, null, 'ticket_resuelto'));
    }

    public function test_tras_el_cooldown_vuelve_a_fluir(): void
    {
        $usuario = (int) DB::table('usuarios')->insertGetId([
            'username' => 'throttle_exp_'.uniqid(),
            'password' => Hash::make('secret'),
            'rol' => 'tecnico',
            'tema' => 'light',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        EmailThrottler::markSent($usuario, null, 'sla_vencido');

        // Forzar el registro a "hace una hora": el cooldown de 30 min quedó atrás.
        DB::table('correos_enviados')
            ->where('usuario_id', $usuario)
            ->where('tipo', 'sla_vencido')
            ->update(['created_at' => now()->subHours(2)]);

        $this->assertTrue(EmailThrottler::canSend($usuario, null, 'sla_vencido'));
    }

    public function test_un_envio_fallido_no_consume_la_ventana_de_cooldown(): void
    {
        $usuario = (int) DB::table('usuarios')->insertGetId([
            'username' => 'throttle_fail_'.uniqid(),
            'password' => Hash::make('secret'),
            'rol' => 'tecnico',
            'tema' => 'light',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Si el listener no llega a markSent() (fallo SMTP), el correo puede
        // reintentarse de inmediato: el intento fallido no dejó evidencia.
        $this->assertTrue(EmailThrottler::canSend($usuario, null, 'ticket_creado'));
        $this->assertTrue(EmailThrottler::canSend($usuario, null, 'ticket_creado'));

        $this->assertDatabaseMissing('correos_enviados', [
            'usuario_id' => $usuario,
            'tipo' => 'ticket_creado',
        ]);
    }
}
