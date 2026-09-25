<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Verifica la autorización server-side (S1 del informe de auditoría):
 * los permisos se exigen por middleware en el servidor, no se simulan
 * ocultando botones en la interfaz.
 */
final class RbacEnforcementTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private User $tecnico;

    private User $consultor;

    private User $operador;

    private int $ticketId;

    private int $empleadoId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->crearUsuario('admin');
        $this->tecnico = $this->crearUsuario('tecnico');
        $this->consultor = $this->crearUsuario('consultor');
        $this->operador = $this->crearUsuario('operador');

        $equipment = DB::table('equipos')->first();
        $equipmentId = $equipment
            ? (int) $equipment->id
            : (int) DB::table('equipos')->insertGetId([
                'codigo_inventario' => 'EQ-RBAC-'.uniqid(),
                'numero_serie' => 'SN-'.uniqid(),
                'tipo' => 'computadora',
                'modelo' => 'Dell Latitude',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        $this->empleadoId = (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Técnico',
            'apellido' => 'RBAC',
            'email' => 'tecnico.rbac.'.uniqid().'@empresa.com',
            'cedula' => 'V-'.rand(10000000, 99999999),
            'rol' => 'tecnico',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->ticketId = (int) DB::table('soportes')->insertGetId([
            'titulo' => 'Ticket para pruebas RBAC',
            'descripcion' => 'Caso de autorización por roles',
            'equipo_id' => $equipmentId,
            'estado' => 'en_proceso',
            'prioridad' => 'media',
            'fecha' => Carbon::now(),
            'usuario_creacion_id' => $this->operador->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    private function crearUsuario(string $rol): User
    {
        return User::firstOrCreate(
            ['username' => "rbac_{$rol}_".uniqid()],
            ['password' => bcrypt('secret'), 'rol' => $rol, 'tema' => 'light']
        );
    }

    public function test_tecnico_no_puede_gestionar_usuarios(): void
    {
        $username = 'intruso_'.uniqid();

        $this->actingAs($this->tecnico)->get('/usuarios')->assertForbidden();
        $this->actingAs($this->tecnico)->post('/usuarios', [
            'username' => $username,
            'password' => 'cualquier-cosa',
            'rol' => 'admin',
        ])->assertForbidden();

        $this->assertSame(0, (int) DB::table('usuarios')->where('username', $username)->count());
    }

    public function test_tecnico_no_puede_ver_auditoria_ni_configuracion(): void
    {
        $this->actingAs($this->tecnico)->get('/auditoria')->assertForbidden();
        $this->actingAs($this->tecnico)->get('/configuracion')->assertForbidden();
    }

    public function test_admin_si_puede_gestionar_usuarios(): void
    {
        $this->actingAs($this->admin)->get('/usuarios')->assertOk();
    }

    public function test_operador_no_puede_operar_el_ciclo_de_vida_del_ticket(): void
    {
        $this->actingAs($this->operador)->post("/soportes/{$this->ticketId}/pausar", [
            'motivo' => 'Intento de pausa por un usuario operador sin permiso.',
        ])->assertForbidden();

        $this->actingAs($this->operador)->post("/soportes/{$this->ticketId}/reabrir", [
            'motivo' => 'Intento de reapertura por un usuario operador sin permiso.',
        ])->assertForbidden();
    }

    public function test_operador_no_puede_ver_reportes(): void
    {
        $this->actingAs($this->operador)->get('/reportes')->assertForbidden();
    }

    public function test_tecnico_si_puede_gestionar_tickets(): void
    {
        $this->actingAs($this->tecnico)
            ->post("/soportes/{$this->ticketId}/reasignar", ['empleado_id' => $this->empleadoId])
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_tecnico_no_puede_eliminar_tickets_pero_el_admin_si(): void
    {
        $this->actingAs($this->tecnico)->delete("/soportes/{$this->ticketId}")->assertForbidden();
        $this->assertDatabaseHas('soportes', ['id' => $this->ticketId]);

        $this->actingAs($this->admin)->delete("/soportes/{$this->ticketId}")->assertRedirect();
        $this->assertDatabaseMissing('soportes', ['id' => $this->ticketId]);
    }

    public function test_consultor_puede_ver_reportes_pero_no_gestionar_catalogos(): void
    {
        $this->actingAs($this->consultor)->get('/reportes')->assertOk();

        $this->actingAs($this->consultor)->post('/categorias', [
            'nombre' => 'Categoría intrusa',
        ])->assertForbidden();
    }

    public function test_operador_puede_comentar_y_el_comentario_queda_a_su_nombre(): void
    {
        $this->actingAs($this->operador)->post("/soportes/{$this->ticketId}/comentarios", [
            'comentario' => 'El usuario operador reporta que la falla persiste desde ayer.',
        ])->assertRedirect();

        $comentario = DB::table('ticket_comentarios')
            ->where('ticket_id', $this->ticketId)
            ->latest('id')
            ->first();

        $this->assertNotNull($comentario);
        // Integridad de autoría: nunca se atribuye al usuario 1 por defecto.
        $this->assertSame($this->operador->id, (int) $comentario->usuario_id);
    }

    public function test_las_notificaciones_solo_las_toca_su_dueno(): void
    {
        $notificationId = (int) DB::table('notificaciones')->insertGetId([
            'usuario_id' => $this->tecnico->id,
            'tipo' => 'info',
            'titulo' => 'Privada',
            'mensaje' => 'Notificación privada del técnico.',
            'leido' => false,
            'created_at' => Carbon::now(),
        ]);

        // Otro usuario no puede marcarla ni borrarla (IDOR corregido).
        $this->actingAs($this->operador)->patch("/notificaciones/{$notificationId}/read");
        $this->assertSame(false, (bool) DB::table('notificaciones')->where('id', $notificationId)->value('leido'));

        $this->actingAs($this->operador)->delete("/notificaciones/{$notificationId}");
        $this->assertDatabaseHas('notificaciones', ['id' => $notificationId]);

        // El dueño sí puede.
        $this->actingAs($this->tecnico)->patch("/notificaciones/{$notificationId}/read");
        $this->assertSame(true, (bool) DB::table('notificaciones')->where('id', $notificationId)->value('leido'));
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get('/usuarios')->assertRedirect('/login');
    }
}
