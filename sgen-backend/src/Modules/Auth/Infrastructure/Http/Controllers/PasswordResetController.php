<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Recuperación self-service de contraseña (Módulo 01 · regla seguridad):
 * el usuario recibe un enlace firmado al correo registrado; el token expira
 * en 60 minutos y es de UN SOLO USO. La política de complejidad se valida
 * igual que en el cambio interno (min 10, mixta, números) e invalidez de
 * sesión ajena se deja al control de sesiones activas.
 */
final class PasswordResetController extends Controller
{
    /** Vista de solicitud "¿Olvidaste tu contraseña?" */
    public function showRequest(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /** Despacha el token al correo del usuario (si existe). */
    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink(['email' => $request->string('email')]);

        return back()->with('status', 'Se ha enviado el enlace si el correo coincide con nuestros registros.');
    }

    /** Vista de restablecimiento con token. */
    public function showReset(string $token): Response
    {
        return Inertia::render('Auth/ResetPassword', ['token' => $token, 'email' => (string) request('email', '')]);
    }

    /** Aplica el cambio e invalida las sesiones abiertas de esa cuenta. */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->password = (string) $request->input('password'); // cast 'hashed' → driver configurado (Argon2id).
                $user->remember_token = Str::random(60);
                $user->must_change_password = false;
                $user->save();

                // Cerrar TODAS las sesiones abiertas de esa cuenta (robo frecuente de credenciales).
                DB::table('sesiones_log')
                    ->where('usuario_id', (int) $user->id)
                    ->whereNull('fecha_fin')
                    ->update(['fecha_fin' => now(), 'motivo_cierre' => 'reset_password']);

                DB::table('sessions')->where('user_id', (int) $user->id)->delete();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Contraseña actualizada. Inicia sesión con la nueva clave.')
            : back()->withErrors(['email' => [trans($status)]]);
    }
}
