<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Flujo: admin restablece a OTRO usuario → cierra sesión → vuelve a entrar
 * como él mismo, sin tokens posteriores y sin que la pantalla aparezca a él.
 */
final class AdminResetFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        RateLimiter::clear('admin_reset_flow_user|127.0.0.1');

        parent::tearDown();
    }

    public function test_credencial_temporal_se_muestra_solo_en_usuarios(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin_reset_flow_user'],
            ['password' => \Illuminate\Support\Facades\Hash::make('Clave-87?Star'), 'rol' => 'admin', 'tema' => 'light', 'activo' => true]
        );

        $tecnicoId = (int) DB::table('usuarios')->insertGetId([
            'username' => 'tecnico_reset_'.uniqid(),
            'password' => \Illuminate\Support\Facades\Hash::make('Una112!@#Vieja'),
            'rol' => 'tecnico',
            'tema' => 'light',
            'activo' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Admin restablece — permanece en Mi Cuenta, porque es su propio acceso.
        $this->actingAs($admin)
            ->post("/usuarios/{$tecnicoId}/restablecer")
            ->assertRedirect();

        // El flash quedó registrado una sola vez.
        $claveSesion = $this->assertSessionHasTemporalPassword(session());
        $this->assertIsArray($claveSesion);

        // El admin sale y vuelve: jamás aparece la credencial ajena en el panel.
        $this->post('/logout');

        $this->post('/login', [
            'username' => 'admin_reset_flow_user',
            'password' => 'Clave-87?Star',
        ])->assertRedirect();

        $this->assertAuthenticated();

        // Y después de ese login, no insinúa nada avanzar como el técnico.
        $this->get('/dashboard')->assertOk()->assertSessionMissing('temp_password');
    }

    private function assertSessionHasTemporalPassword(mixed $session): ?array
    {
        $payload = session()->get('temp_password');
        if ($payload !== null) {
            $this->assertIsArray($payload);
            $this->assertArrayHasKey('user_id', $payload);
            $this->assertArrayHasKey('password', $payload);
        }
        return $payload;
    }
}
