<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Restablecimiento operado por admin + cambio obligatorio de contraseña:
 * la cuarentena funciona hasta que el usuario define su propia clave.
 */
final class AdminPasswordResetTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_pwdreset_test'],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
    }

    public function test_admin_restablece_contrasena_y_la_cuenta_queda_en_cuarentena_hasta_cambiarla(): void
    {
        $user = User::create([
            'username' => 'target_reset_'.uniqid(),
            'password' => Hash::make('Temporal-Vieja1'),
            'rol' => 'operador',
            'tema' => 'light',
        ]);

        // El admin restablece
        $response = $this->actingAs($this->admin)->post("/usuarios/{$user->id}/restablecer");
        $response->assertRedirect();
        $response->assertSessionHas('temp_password');
        $temporal = session('temp_password')['password'] ?? null;
        $this->assertNotEmpty($temporal);

        // La cuenta queda marcada para cambio obligatorio
        $this->assertDatabaseHas('usuarios', ['id' => $user->id, 'must_change_password' => true]);

        // El usuario entra con la clave temporal y es confinado a la pantalla de cambio
        $login = $this->post('/login', ['username' => $user->username, 'password' => $temporal]);
        $login->assertRedirect();

        $this->actingAs($user->fresh());
        $restringido = $this->get('/inventario');
        $restringido->assertRedirect(route('cuenta.contrasena'));

        // Cambia la contraseña (la política fuerte se exige aquí también)
        $this->post('/configuracion/password', [
            'current_password' => $temporal,
            'new_password' => 'Nueva.Segura2026#fort',
            'new_password_confirmation' => 'Nueva.Segura2026#fort',
        ])->assertRedirect();

        // Bandera liberada y navegación normal
        $this->assertDatabaseHas('usuarios', ['id' => $user->id, 'must_change_password' => false]);

        $resp = $this->get('/inventario');
        if ($resp->status() !== 200) {
            throw new \RuntimeException('INVENTARIO respondió '.$resp->status().' → Location: '.($resp->headers->get('Location') ?? '(sin header)'));
        }
        $resp->assertOk();
        $this->assertTrue(Hash::check('Nueva.Segura2026#fort', DB::table('usuarios')->where('id', $user->id)->value('password')));
    }

    public function test_tecnico_no_puede_restablecer_contrasenas_ajenas(): void
    {
        $tecnico = User::firstOrCreate(
            ['username' => 'tecnico_pwdreset_test'],
            ['password' => Hash::make('secret'), 'rol' => 'tecnico', 'tema' => 'light']
        );

        $victima = User::create(['username' => 'victima_'.uniqid(), 'password' => Hash::make('x'), 'rol' => 'operador', 'tema' => 'light']);

        $this->actingAs($tecnico)->post("/usuarios/{$victima->id}/restablecer")->assertForbidden();
        $this->assertDatabaseHas('usuarios', ['id' => $victima->id, 'must_change_password' => false]);
    }

    public function test_usuario_creado_por_admin_nace_con_cambio_obligatorio(): void
    {
        $emailNuevo = 'nuevo_'.uniqid().'@empresa.com';
        $this->actingAs($this->admin)->post('/usuarios', [
            'username' => 'nuevo_'.uniqid(),
            'password' => 'Inicial.Segura2026#ok',
            'rol' => 'consultor',
            'email' => $emailNuevo, // regla de datos maestros: email obligatorio
        ])->assertRedirect(route('usuarios.index'));

        $id = DB::table('usuarios')->where('username', 'like', 'nuevo_%')->latest('id')->value('id');
        $this->assertEquals(1, (int) DB::table('usuarios')->where('id', $id)->value('must_change_password'));
    }
}
