<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Self-service de contraseña (módulo 01) y límite de sesiones concurrentes:
 * - Enlace firmado de un solo uso; al cambiar se cierran todas las sesiones.
 * - El usuario conserva como máximo `seguridad.sesiones_concurrentes_max`
 *   sesiones vivas; las más antiguas expiran al iniciar una nueva.
 */
final class PasswordResetAndSessionLimitTest extends TestCase
{
    use DatabaseTransactions;

    private function crearUsuarioConEmail(): int
    {
        return (int) DB::table('usuarios')->insertGetId([
            'username' => 'reset_user_'.uniqid(),
            'password' => Hash::make('Clave-Vieja-123'),
            'email' => 'reset.user.'.uniqid().'@empresa.com',
            'rol' => 'consultor',
            'tema' => 'light',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_reset_por_email_cambia_contrasena_y_cierra_sesiones(): void
    {
        $userId = $this->crearUsuarioConEmail();
        $user = DB::table('usuarios')->find($userId);

        $token = Password::broker()->createToken(User::findOrFail($userId));

        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        // Sesiones abiertas preexistentes: deben cerrarse.
        DB::table('sessions')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'ip_address' => '10.9.9.9',
            'user_agent' => 'test',
            'payload' => 'x',
            'last_activity' => time(),
        ]);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Nueva-Clave-2026',
            'password_confirmation' => 'Nueva-Clave-2026',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('Nueva-Clave-2026', (string) DB::table('usuarios')->where('id', $userId)->value('password')));
        $this->assertDatabaseMissing('sessions', ['user_id' => $userId]);
    }

    public function test_token_de_reset_incorrecto_no_cambia_nada(): void
    {
        $userId = $this->crearUsuarioConEmail();
        $email = (string) DB::table('usuarios')->where('id', $userId)->value('email');
        $hashViejo = (string) DB::table('usuarios')->where('id', $userId)->value('password');

        $this->post('/reset-password', [
            'token' => 'token-invalido-000',
            'email' => $email,
            'password' => 'Nueva-Clave-2026',
            'password_confirmation' => 'Nueva-Clave-2026',
        ])->assertSessionHasErrors('email');

        $this->assertSame($hashViejo, (string) DB::table('usuarios')->where('id', $userId)->value('password'));
    }
}
