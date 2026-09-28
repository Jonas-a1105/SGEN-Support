<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fuerza el cambio de contraseña en cuentas con clave temporal
 * (creadas o restablecidas por administración). El usuario queda
 * confinado a la pantalla de cambio hasta definir una contraseña fuerte.
 */
final class EnsurePasswordChanged
{
    private const ALLOWED_ROUTES = [
        'cuenta.contrasena',
        'configuracion.password',
        'logout',
        'login',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && (bool) ($user->must_change_password ?? false)) {
            // La instancia del guard puede estar memoizada en el proceso:
            // verificar SIEMPRE contra la base de datos cuando la bandera
            // diga "pendiente" (tras el cambio, libera sin re-login).
            $mustChange = (bool) DB::table('usuarios')->where('id', $user->id)->value('must_change_password');

            if ($mustChange) {
                $routeName = (string) ($request->route()?->getName() ?? '');

                $allowed = in_array($routeName, self::ALLOWED_ROUTES, true)
                    || str_starts_with($routeName, 'notificaciones.');

                if (! $allowed) {
                    return redirect()
                        ->route('cuenta.contrasena')
                        ->with('warning', 'Por seguridad debes establecer una nueva contraseña antes de continuar.');
                }
            }
        }

        return $next($request);
    }
}
