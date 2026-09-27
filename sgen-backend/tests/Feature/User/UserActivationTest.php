<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Desactivación de cuentas (reglas #26 y #43 del checklist maestro):
 * quien tiene historial se desactiva en vez de borrarse; la cuenta inactiva
 * pierde el acceso de inmediato (sesiones revocadas) y ninguna protección
 * estructural (último admin, tickets activos) se rompe.
 */
final class UserActivationTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_activation_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
    }

    private function crearUsuarioActivo(string $rol = 'consultor'): User
    {
        $id = (int) DB::table('usuarios')->insertGetId([
            'username' => 'activacion_'.uniqid(),
            'password' => \Illuminate\Support\Facades\Hash::make('Clave-Valida-123'),
            'rol' => $rol,
            'tema' => 'light',
            'activo' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return User::findOrFail($id);
    }

    public function test_desactivar_y_reactivar_un_usuario(): void
    {
        $usuario = $this->crearUsuarioActivo();

        $this->actingAs($this->admin)
            ->post("/usuarios/{$usuario->id}/alternar-estado")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('usuarios', ['id' => $usuario->id, 'activo' => false]);

        $this->actingAs($this->admin)
            ->post("/usuarios/{$usuario->id}/alternar-estado")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('usuarios', ['id' => $usuario->id, 'activo' => true]);
    }

    public function test_desactivar_revoca_sesiones_vivas(): void
    {
        $usuario = $this->crearUsuarioActivo();

        DB::table('sessions')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $usuario->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'fixture',
            'last_activity' => time(),
        ]);

        $this->actingAs($this->admin)->post("/usuarios/{$usuario->id}/alternar-estado");

        $this->assertDatabaseMissing('sessions', ['user_id' => $usuario->id]);
    }

    public function test_usuario_desactivado_no_puede_iniciar_sesion(): void
    {
        $usuario = $this->crearUsuarioActivo();

        $this->actingAs($this->admin)
            ->post("/usuarios/{$usuario->id}/alternar-estado")
            ->assertSessionHas('success');

        // Cerrar la sesión del administrador para probar el login como invitado.
        $this->post('/logout');

        $this->post('/login', [
            'username' => $usuario->username,
            'password' => 'Clave-Valida-123',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
        $this->assertDatabaseHas('intentos_login', [
            'username' => $usuario->username,
            'exitoso' => false,
        ]);
    }

    public function test_nadie_puede_desactivar_su_propia_cuenta_en_sesion(): void
    {
        // Aunque hubiera otro administrador activo, la auto-desactivación
        // desde la propia sesión siempre está prohibida (anti auto-bloqueo).
        $this->actingAs($this->admin)
            ->post("/usuarios/{$this->admin->id}/alternar-estado")
            ->assertSessionHas('error', fn (string $mensaje) => str_contains($mensaje, 'tu propia cuenta'));

        $this->assertDatabaseHas('usuarios', ['id' => $this->admin->id, 'activo' => true]);
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_nadie_puede_eliminar_su_propia_cuenta_en_sesion(): void
    {
        $this->actingAs($this->admin)
            ->delete("/usuarios/{$this->admin->id}")
            ->assertSessionHas('error', fn (string $mensaje) => str_contains($mensaje, 'tu propia cuenta'));

        $this->assertDatabaseHas('usuarios', ['id' => $this->admin->id]);
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_no_se_puede_desactivar_al_ultimo_administrador(): void
    {
        // Reducir el escenario a un único administrador.
        DB::table('usuarios')->where('rol', 'admin')->where('id', '!=', $this->admin->id)->delete();

        $this->actingAs($this->admin)
            ->post("/usuarios/{$this->admin->id}/alternar-estado")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('usuarios', ['id' => $this->admin->id, 'activo' => true]);
    }

    public function test_no_se_desactiva_tecnico_con_tickets_activos(): void
    {
        $empleadoId = (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Técnico',
            'apellido' => 'ConCarga',
            'email' => 'tecnico.activacion.'.uniqid().'@empresa.com',
            'cedula' => 'V-'.random_int(10000000, 99999999),
            'rol' => 'tecnico',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $usuarioId = (int) DB::table('usuarios')->insertGetId([
            'username' => 'tecnico_carga_'.uniqid(),
            'password' => \Illuminate\Support\Facades\Hash::make('secret'),
            'rol' => 'tecnico',
            'tema' => 'light',
            'activo' => true,
            'empleado_id' => $empleadoId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $equipo = DB::table('equipos')->first();
        $equipoId = $equipo
            ? (int) $equipo->id
            : (int) DB::table('equipos')->insertGetId([
                'codigo_inventario' => 'EQ-ACT-'.uniqid(),
                'numero_serie' => 'SN-'.uniqid(),
                'tipo' => 'computadora',
                'modelo' => 'Dell Latitude',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        DB::table('soportes')->insert([
            'titulo' => 'Ticket activo del técnico',
            'descripcion' => 'Debe impedir la desactivación',
            'equipo_id' => $equipoId,
            'empleado_id' => $empleadoId,
            'estado' => 'en_proceso',
            'prioridad' => 'media',
            'fecha' => Carbon::now(),
            'usuario_creacion_id' => $this->admin->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->actingAs($this->admin)
            ->post("/usuarios/{$usuarioId}/alternar-estado")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('usuarios', ['id' => $usuarioId, 'activo' => true]);
    }
}
