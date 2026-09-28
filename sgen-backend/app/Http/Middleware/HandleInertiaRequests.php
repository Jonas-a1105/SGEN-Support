<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Rbac\PermissionCatalog;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => fn (): array => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'username' => $request->user()->username,
                    'rol' => $request->user()->rol,
                    'tema' => $request->user()->tema,
                    'must_change_password' => (bool) ($request->user()->must_change_password ?? false),
                    'empleado_id' => $request->user()->empleado_id,
                    'departamento_id' => $request->user()->departamento_id,
                    // Conjunto de permisos efectivos (RBAC): la navegación se
                    // corta a lo permitido en vez de declarar lo inaccessible.
                    'permissions' => PermissionCatalog::permissionsForRole((string) $request->user()->rol),
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                // Clave temporal de restablecimiento: se revela SOLO en la sección
                // de usuarios (otro página no se suelta — no es un aviso global).
                'temp_password' => $request->routeIs('usuarios.*')
                    ? $request->session()->pull('temp_password')
                    : null,
            ],
        ];
    }
}
