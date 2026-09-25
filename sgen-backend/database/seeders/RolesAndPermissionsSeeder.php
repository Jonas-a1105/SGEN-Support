<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Rbac\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Provisiona el catálogo RBAC (roles + permisos + su matriz) en la base
 * de datos destino. Es idempotente: seguro de ejecutar en cada deploy.
 */
final class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionCatalog::permissions() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (PermissionCatalog::ROLES as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions(PermissionCatalog::permissionsForRole($roleName));
        }
    }
}
