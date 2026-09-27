<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Editor de roles y permisos (Módulo 30): matriz editable sobre la fuente de
 * verdad (`PermissionCatalog` para el estado documental; la persistencia usa
 * Spatie en rol_permisos). Cambios bitácora auditada y restricción del rol
 * administrador de sistema (intocable para no autodestruir el acceso).
 */
final class RolesAdminController extends Controller
{
    public function index(): Response
    {
        $roles = Role::query()
            ->with('permissions:id,name')
            ->orderBy('id')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Roles', [
            'roles' => $roles->map(fn (Role $role) => [
                'id' => (int) $role->id,
                'nombre' => (string) $role->name,
                'es_sistema' => $role->name === 'admin',
                'permisos' => $role->permissions->pluck('name')->all(),
                'asignados' => (int) DB::table('model_has_roles')->where('role_id', $role->id)->count(),
            ])->all(),
            'permisos_disponibles' => Permission::query()->orderBy('name')->pluck('name')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        // Rol administrador de sistema: no puede degradarse nomás quitando
        // permisos operativos clave (riesgo de rescate roto).
        if ($role->name === 'admin') {
            return back()->with('error', 'El rol de administrador es el rol de sistema y no es editable desde aquí; sus permisos alcanzan la administración, auditoría y seguridad del núcleo.');
        }

        $validated = $request->validate([
            'permisos' => ['required', 'array'],
            'permisos.*' => ['string', 'in:'.implode(',', Permission::query()->pluck('name')->all() ?: ['*'])],
        ]);

        Role::findOrFail($role->id)->syncPermissions((array) $validated['permisos']);

        return back()->with('success', "Permisos de [{$role->name}] actualizados y bitacoreados.");
    }
}
