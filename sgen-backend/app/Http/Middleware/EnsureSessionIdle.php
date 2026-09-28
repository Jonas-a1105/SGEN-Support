<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Config\ConfiguracionGlobal;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierre por inactividad (checklist #50): cualquier petición autenticada
 * cuenta como actividad; si la última supera los minutos configurados
 * (`seguridad.idle_minutos`), la sesión se cierra y el cierre queda
 * etiquetado en sesiones_log para la auditoría forense.
 */
final class EnsureSessionIdle
{
    private const SESSION_KEY = 'sgen.ultima_actividad';

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && $request->hasSession()) {
            $usuario = Auth::user();

            // Una cuenta desactivada no debe seguir operando por la cookie de
            // "recordarme": se fuerza el cierre en cada petición autenticada.
            // Solo `false` explícito desactiva: NULL legacy equivale a activo.
            if ($usuario instanceof User && $usuario->activo === false) {
                $this->cerrarRegistroSesion($request, 'cuenta_desactivada');

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->with('error', 'Tu cuenta fue desactivada. Contacta a un administrador.');
            }

            $ultima = $request->session()->get(self::SESSION_KEY);

            if ($ultima !== null) {
                $limiteMinutos = max(1, ConfiguracionGlobal::entero('seguridad.idle_minutos', 30));
                $venceEn = Carbon::parse((string) $ultima)->addMinutes($limiteMinutos);

                if (Carbon::now()->greaterThan($venceEn)) {
                    $this->cerrarRegistroSesion($request, 'inactividad');

                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()
                        ->route('login')
                        ->with('error', 'Tu sesión se cerró por inactividad. Inicia sesión de nuevo.');
                }
            }

            // Toda petición autenticada renueva el sello de actividad.
            $request->session()->put(self::SESSION_KEY, Carbon::now()->toDateTimeString());
        }

        return $next($request);
    }

    /**
     * Cierra el registro forense de la sesión actual con el motivo dado.
     */
    private function cerrarRegistroSesion(Request $request, string $motivo): void
    {
        $registroId = $request->session()->get('sesion_log_id');

        if ($registroId !== null) {
            DB::table('sesiones_log')
                ->where('id', (int) $registroId)
                ->whereNull('fecha_fin')
                ->update(['fecha_fin' => Carbon::now(), 'motivo_cierre' => $motivo]);
        }
    }
}
