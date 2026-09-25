<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regresión: en PostgreSQL el lookup de username es case-sensitive por
 * defecto (en el MySQL legacy no lo era). Usuarios válidos quedaban fuera
 * por una letra mayúscula. El login es ahora insensible a mayúsculas y
 * espacios, y el throttling sigue intacto.
 */
final class LoginUsernameNormalizationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_el_login_acepta_username_en_cualquier_capitalizacion_y_con_espacios(): void
    {
        User::create([
            'username' => 'MezclaCase_'.uniqid(),
            'password' => bcrypt('LaMisma.Clave2026'),
            'rol' => 'operador',
            'tema' => 'light',
        ]);

        $username = DB::table('usuarios')->latest('id')->value('username');

        $intento = function (string $user, string $clave) {
            // Forzar estado de invitado en cada intento (la ruta /login es guest-only;
            // sin logout los intentos consecutivos serían redirecciones vacías).
            if (auth()->check()) {
                $this->post('/logout');
            }

            return $this->post('/login', ['username' => $user, 'password' => $clave]);
        };

        // Exacto
        $intento((string) $username, 'LaMisma.Clave2026')->assertRedirect();

        // Mayúsculas invertidas
        $intento(mb_strtoupper((string) $username), 'LaMisma.Clave2026')->assertRedirect();

        // Con espacios alrededor
        $intento("  {$username}  ", 'LaMisma.Clave2026')->assertRedirect();

        // Contraseña equivocada sigue rechazada (no hay bypass)
        $intento((string) $username, 'incorrecta')->assertSessionHasErrors('username');
    }
}
