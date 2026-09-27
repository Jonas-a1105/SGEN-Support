<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Checklist #50 (idle timeout) y sesiones forenses: la actividad se sella
 * por petición; superado el límite, la sesión se cierra con motivo
 * "inactividad" y el logout normal queda como "manual".
 */
final class SessionIdleTimeoutTest extends TestCase
{
    use DatabaseTransactions;

    private function crearUsuario(): User
    {
        $id = (int) DB::table('usuarios')->insertGetId([
            'username' => 'idle_'.uniqid(),
            'password' => Hash::make('Clave-Valida-123'),
            'rol' => 'consultor',
            'tema' => 'light',
            'activo' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return User::findOrFail($id);
    }

    protected function tearDown(): void
    {
        // Limpieza de rate limiting para no contaminar otros tests.
        foreach (DB::table('usuarios')->where('username', 'like', 'idle_%')->pluck('username') as $username) {
            RateLimiter::clear(strtolower((string) $username).'|127.0.0.1');
        }

        parent::tearDown();
    }

    public function test_el_login_abre_registro_y_el_logout_lo_cierra_como_manual(): void
    {
        $usuario = $this->crearUsuario();

        $this->post('/login', [
            'username' => $usuario->username,
            'password' => 'Clave-Valida-123',
        ])->assertRedirect();

        $registro = DB::table('sesiones_log')
            ->where('usuario_id', $usuario->id)
            ->whereNull('fecha_fin')
            ->latest('id')
            ->first();

        $this->assertNotNull($registro, 'El login debe abrir una fila forense.');

        $this->post('/logout')->assertRedirect();

        $cerrado = DB::table('sesiones_log')->where('id', $registro->id)->first();
        $this->assertNotNull($cerrado->fecha_fin);
        $this->assertSame('manual', $cerrado->motivo_cierre);
    }

    public function test_la_inactividad_cierra_la_sesion_con_motivo(): void
    {
        $usuario = $this->crearUsuario();

        $this->post('/login', [
            'username' => $usuario->username,
            'password' => 'Clave-Valida-123',
        ])->assertRedirect();

        // Simular actividad antigua: 2 horas sin peticiones.
        $this->withSession(['sgen.ultima_actividad' => Carbon::now()->subHours(2)->toDateTimeString()])
            ->get('/dashboard')
            ->assertRedirect(route('login'));

        $registro = DB::table('sesiones_log')
            ->where('usuario_id', $usuario->id)
            ->latest('id')
            ->first();

        $this->assertSame('inactividad', $registro->motivo_cierre);
        $this->assertNotNull($registro->fecha_fin);
        $this->assertGuest();
    }

    public function test_actividad_reciente_mantiene_la_sesion_viva(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)
            ->withSession(['sgen.ultima_actividad' => Carbon::now()->subMinutes(5)->toDateTimeString()])
            ->get('/dashboard')
            ->assertOk();

        $this->assertAuthenticatedAs($usuario);
    }
}
