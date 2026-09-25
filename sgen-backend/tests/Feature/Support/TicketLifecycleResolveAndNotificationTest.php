<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class TicketLifecycleResolveAndNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private int $empleadoId;
    private int $techUserId;
    private int $requesterUserId;
    private int $equipmentId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['username' => 'admin_lifecycle_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );

        // Crear departamento
        $deptId = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Tecnología e Infraestructura ' . uniqid(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Crear empleado técnico
        $this->empleadoId = (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Carlos',
            'apellido' => 'Técnico',
            'email' => 'carlos.tech.' . uniqid() . '@empresa.com',
            'cedula' => 'V-' . rand(10000000, 99999999),
            'cargo' => 'Técnico de Soporte',
            'departamento_id' => $deptId,
            'rol' => 'tecnico',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Crear usuario vinculado al técnico
        $techUser = User::firstOrCreate(
            ['username' => 'carlos_tech_' . uniqid()],
            [
                'password' => bcrypt('secret'),
                'rol' => 'tecnico',
                'empleado_id' => $this->empleadoId,
                'tema' => 'dark',
            ]
        );
        $this->techUserId = $techUser->id;

        // Crear usuario solicitante
        $requesterUser = User::firstOrCreate(
            ['username' => 'ana_solicitante_' . uniqid()],
            [
                'password' => bcrypt('secret'),
                'rol' => 'consultor',
                'tema' => 'light',
            ]
        );
        $this->requesterUserId = $requesterUser->id;

        // Crear equipo
        $equipment = DB::table('equipos')->first();
        if ($equipment) {
            $this->equipmentId = $equipment->id;
        } else {
            $this->equipmentId = (int) DB::table('equipos')->insertGetId([
                'codigo_inventario' => 'EQ-TEST-' . uniqid(),
                'numero_serie' => 'SN-' . uniqid(),
                'tipo' => 'computadora',
                'modelo' => 'Dell Latitude',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    public function test_can_resolve_ticket_and_trigger_notification_and_calculate_attention_time(): void
    {
        $ticketId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Falla de pantalla azul en equipo contable',
            'descripcion' => 'Al abrir el sistema contable arroja pantallazo azul BSOD',
            'equipo_id' => $this->equipmentId,
            'estado' => 'en_proceso',
            'prioridad' => 'media',
            'fecha' => Carbon::now()->subHours(3),
            'fecha_asignacion' => Carbon::now()->subHours(2),
            'empleado_id' => $this->empleadoId,
            'usuario_creacion_id' => $this->requesterUserId,
            'tiempo_pausado_minutos' => 15,
            'created_at' => Carbon::now()->subHours(3),
            'updated_at' => Carbon::now()->subHours(2),
        ]);

        $response = $this->actingAs($this->user)->put("/soportes/{$ticketId}", [
            'estado' => 'resuelto',
            'solucion' => 'Se reinstaló el controlador de video defectuoso y se verificó funcionamiento.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Validar base de datos
        $ticket = DB::table('soportes')->where('id', $ticketId)->first();
        $this->assertNotNull($ticket);
        $this->assertSame('resuelto', $ticket->estado);
        $this->assertNotNull($ticket->fecha_resolucion);
        $this->assertNotNull($ticket->tiempo_atencion_minutos);
        $this->assertGreaterThan(0, $ticket->tiempo_atencion_minutos);
        $this->assertSame('Se reinstaló el controlador de video defectuoso y se verificó funcionamiento.', $ticket->solucion);

        // Validar comentario formal insertado
        $comment = DB::table('ticket_comentarios')->where('ticket_id', $ticketId)->first();
        $this->assertNotNull($comment);
        $this->assertStringContainsString('Ticket RESUELTO', $comment->comentario);

        // Validar notificación al solicitante
        $notif = DB::table('notificaciones')
            ->where('usuario_id', $this->requesterUserId)
            ->where('tipo', 'ticket_estado_cambiado')
            ->first();
        $this->assertNotNull($notif);
        $this->assertSame('Ticket Resuelto', $notif->titulo);
    }

    public function test_can_reopen_resolved_ticket_with_motive_and_notify_technician(): void
    {
        $ticketId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Impresora térmica atascada',
            'descripcion' => 'No salen los tickets de almacén',
            'equipo_id' => $this->equipmentId,
            'estado' => 'resuelto',
            'prioridad' => 'alta',
            'fecha' => Carbon::now()->subDays(1),
            'empleado_id' => $this->empleadoId,
            'usuario_creacion_id' => $this->requesterUserId,
            'solucion' => 'Se destrabó el rodillo de papel',
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1),
        ]);

        // Validación: motivo corto debe fallar
        $failResponse = $this->actingAs($this->user)->post("/soportes/{$ticketId}/reabrir", [
            'motivo' => 'falla',
        ]);
        $failResponse->assertSessionHasErrors(['motivo']);

        // Envío válido
        $response = $this->actingAs($this->user)->post("/soportes/{$ticketId}/reabrir", [
            'motivo' => 'El rodillo volvió a trabarse inmediatamente después de imprimir 3 hojas.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Validar base de datos
        $ticket = DB::table('soportes')->where('id', $ticketId)->first();
        $this->assertSame('en_proceso', $ticket->estado);

        // Validar comentario en historial
        $comment = DB::table('ticket_comentarios')
            ->where('ticket_id', $ticketId)
            ->where('comentario', 'like', '%Ticket REABIERTO%')
            ->first();
        $this->assertNotNull($comment);

        // Validar notificación al técnico asignado
        $notif = DB::table('notificaciones')
            ->where('usuario_id', $this->techUserId)
            ->where('tipo', 'ticket_estado_cambiado')
            ->first();
        $this->assertNotNull($notif);
        $this->assertSame('Ticket Reabierto', $notif->titulo);
    }

    public function test_reassigning_technician_triggers_notification(): void
    {
        $ticketId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Configuración de VPN remota',
            'descripcion' => 'El usuario necesita acceso a la VPN',
            'equipo_id' => $this->equipmentId,
            'estado' => 'pendiente',
            'prioridad' => 'baja',
            'fecha' => Carbon::now(),
            'usuario_creacion_id' => $this->requesterUserId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)->post("/soportes/{$ticketId}/reasignar", [
            'empleado_id' => $this->empleadoId,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Validar que el técnico recibió la notificación
        $notif = DB::table('notificaciones')
            ->where('usuario_id', $this->techUserId)
            ->where('tipo', 'ticket_asignado')
            ->first();
        $this->assertNotNull($notif);
        $this->assertSame('Ticket Asignado', $notif->titulo);
        $this->assertStringContainsString("ticket #{$ticketId}", $notif->mensaje);
    }
}
