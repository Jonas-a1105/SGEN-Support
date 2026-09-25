<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Notification\Infrastructure\Mail\TicketNotificationMail;
use Tests\TestCase;

/**
 * Canal correo conectado a los eventos de dominio: solo viaja cuando el
 * destinatario tiene email registrado; nunca bloquea la operación.
 */
final class TicketMailNotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_reasignar_un_ticket_envia_correo_al_tecnico_con_email(): void
    {
        Mail::fake();

        $admin = User::firstOrCreate(['username' => 'admin_mail_test'], ['password' => bcrypt('secret'), 'rol' => 'admin', 'tema' => 'light']);

        $empleadoId = (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Tecnica', 'apellido' => 'Correo', 'email' => 'tec.correo.'.uniqid().'@empresa.test',
            'cedula' => 'V-'.rand(10000000, 99999999), 'rol' => 'tecnico',
            'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
        ]);

        $tecnico = User::firstOrCreate(
            ['username' => 'tec_mail_'.uniqid()],
            ['password' => bcrypt('secret'), 'rol' => 'tecnico', 'tema' => 'light', 'empleado_id' => $empleadoId, 'email' => 'destino@empresa.test']
        );

        $ticketId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket para correo de asignación', 'descripcion' => 'Caso E2E de correo',
            'equipo_id' => (int) (DB::table('equipos')->value('id') ?? DB::table('equipos')->insertGetId([
                'codigo_inventario' => 'EQ-MAIL-'.uniqid(), 'numero_serie' => 'SN-'.uniqid(),
                'tipo' => 'computadora', 'modelo' => 'Dell', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
            ])),
            'estado' => 'pendiente', 'prioridad' => 'alta', 'fecha' => Carbon::now(),
            'usuario_creacion_id' => $admin->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
        ]);

        $this->actingAs($admin)->post("/soportes/{$ticketId}/reasignar", ['empleado_id' => $empleadoId])->assertRedirect();

        Mail::assertSent(TicketNotificationMail::class, fn ($mail) => $mail->hasTo('destino@empresa.test'));
        $this->assertDatabaseHas('notificaciones', ['usuario_id' => $tecnico->id, 'tipo' => 'ticket_asignado']);
    }

    public function test_sin_email_registrado_no_se_envia_correo_pero_si_notificacion(): void
    {
        Mail::fake();

        $admin = User::firstOrCreate(['username' => 'admin_mail_test'], ['password' => bcrypt('secret'), 'rol' => 'admin', 'tema' => 'light']);

        $empleadoId = (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Tecnica', 'apellido' => 'SinMail', 'email' => 'sinmail.'.uniqid().'@empresa.test',
            'cedula' => 'V-'.rand(10000000, 99999999), 'rol' => 'tecnico',
            'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
        ]);

        $tecnico = User::firstOrCreate(
            ['username' => 'tec_sinmail_'.uniqid()],
            ['password' => bcrypt('secret'), 'rol' => 'tecnico', 'tema' => 'light', 'empleado_id' => $empleadoId]
        );

        $ticketId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket sin email', 'descripcion' => 'Caso sin correo',
            'equipo_id' => (int) DB::table('equipos')->value('id'),
            'estado' => 'pendiente', 'prioridad' => 'media', 'fecha' => Carbon::now(),
            'usuario_creacion_id' => $admin->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
        ]);

        $this->actingAs($admin)->post("/soportes/{$ticketId}/reasignar", ['empleado_id' => $empleadoId])->assertRedirect();

        Mail::assertNothingSent();
        $this->assertDatabaseHas('notificaciones', ['usuario_id' => $tecnico->id, 'tipo' => 'ticket_asignado']);
    }
}
