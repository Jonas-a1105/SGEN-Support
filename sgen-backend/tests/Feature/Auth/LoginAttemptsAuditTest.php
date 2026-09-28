<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Registro forense de autenticación + bloqueo temporal con alerta:
 * cada intento (exitoso o no) queda en intentos_login con IP/agente, y al
 * agotarse los intentos permitidos la administración recibe una alerta
 * exactamente una vez por ráfaga de bloqueo.
 */
final class LoginAttemptsAuditTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_login_audit_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );

        User::firstOrCreate(
            ['username' => 'usuario_objetivo_login'],
            ['password' => Hash::make('clave-valida-123'), 'rol' => 'consultor', 'tema' => 'light']
        );
    }

    protected function tearDown(): void
    {
        // La llave de rate limiting persiste entre procesos; limpiarla evita
        // que una ráfaga de bloqueo del test contamine al resto de la suite.
        RateLimiter::clear('usuario_objetivo_login|127.0.0.1');

        parent::tearDown();
    }

    public function test_un_login_fallido_queda_registrado(): void
    {
        $this->post('/login', [
            'username' => 'usuario_objetivo_login',
            'password' => 'clave-incorrecta',
        ])->assertSessionHasErrors('username');

        $this->assertDatabaseHas('intentos_login', [
            'username' => 'usuario_objetivo_login',
            'exitoso' => false,
        ]);
    }

    public function test_un_login_exitoso_queda_registrado(): void
    {
        $this->post('/login', [
            'username' => 'usuario_objetivo_login',
            'password' => 'clave-valida-123',
        ])->assertRedirect();

        $this->assertDatabaseHas('intentos_login', [
            'username' => 'usuario_objetivo_login',
            'exitoso' => true,
        ]);
    }

    public function test_al_bloquearse_la_cuenta_se_alertan_los_administradores(): void
    {
        $intentosMax = 5;

        for ($i = 0; $i < $intentosMax; $i++) {
            $this->post('/login', [
                'username' => 'usuario_objetivo_login',
                'password' => "incorrecta-{$i}",
            ])->assertSessionHasErrors('username');
        }

        // Alerta única al cruzar el umbral, dirigida a la administración.
        $alertas = (int) DB::table('notificaciones')
            ->where('tipo', 'seguridad_login')
            ->where('usuario_id', $this->admin->id)
            ->count();

        $this->assertSame(1, $alertas, 'El bloqueo debe alertar exactamente una vez por ráfaga.');

        // Siguiente intento: bloqueado por rate limiting (sin nueva alerta).
        $this->post('/login', [
            'username' => 'usuario_objetivo_login',
            'password' => 'clave-valida-123',
        ])->assertSessionHasErrors('username');

        $alertasTrasBloqueo = (int) DB::table('notificaciones')
            ->where('tipo', 'seguridad_login')
            ->where('usuario_id', $this->admin->id)
            ->count();

        $this->assertSame(1, $alertasTrasBloqueo, 'No deben generarse alertas repetidas durante el bloqueo.');
    }
}
