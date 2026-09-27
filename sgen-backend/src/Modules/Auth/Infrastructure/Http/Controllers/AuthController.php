<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Auth\Infrastructure\Http\Requests\LoginRequest;
use App\Support\Config\ConfiguracionGlobal;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Límite de sesiones concurrentes (seguridad #50-compañera):
        // conserva las N más recientes; las demás quedan cerradas de inmediato.
        $sesionesMax = max(1, ConfiguracionGlobal::entero('seguridad.sesiones_concurrentes_max', 3));
        $userId = (int) $request->user()->id;

        // Más allá de las N-1 más recientes (la actual deja un asiento libre):
        // eliminar las más viejas vía SQL directo.
        $sesionesExtra = DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $request->session()->getId())
            ->orderBy('last_activity', 'asc')
            ->skip(max(0, $sesionesMax - 1))
            ->pluck('id');

        if ($sesionesExtra->isNotEmpty()) {
            DB::table('sessions')->whereIn('id', $sesionesExtra)->delete();
        }

        // Registro forense de la sesión (auditoría): se abre al autenticar y
        // solo se cierra por logout manual o por expiración de inactividad.
        $user = $request->user();
        $sesionLogId = DB::table('sesiones_log')->insertGetId([
            'usuario_id' => (int) $user->id,
            'username' => (string) $user->username,
            'ip' => $request->ip() !== null ? mb_substr((string) $request->ip(), 0, 45) : null,
            'user_agent' => $request->userAgent() !== null ? mb_substr((string) $request->userAgent(), 0, 512) : null,
            'fecha_inicio' => Carbon::now(),
            'fecha_fin' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $request->session()->put('sesion_log_id', (int) $sesionLogId);

        return redirect()->intended(route('inventario.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        // Cierre manual: se sella el registro forense antes de invalidar.
        $sesionLogId = $request->session()->get('sesion_log_id');
        if ($sesionLogId !== null) {
            DB::table('sesiones_log')
                ->where('id', (int) $sesionLogId)
                ->whereNull('fecha_fin')
                ->update(['fecha_fin' => Carbon::now(), 'motivo_cierre' => 'manual']);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
