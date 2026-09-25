<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Siembra los usuarios institucionales base.
 *
 * Seguridad (auditoría 2026-09-25): las contraseñas NUNCA están en el
 * código. Se leen de variables de entorno (SEED_ADMIN_PASSWORD, etc.).
 * En entornos no productivos sin variable definida se genera una
 * contraseña aleatoria y se imprime una única vez por consola.
 * En producción, sin variable definida, el usuario simplemente no se crea.
 */
class RbacAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Provisionar catálogo RBAC completo (roles + permisos + matriz)
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Crear o actualizar usuarios institucionales base
        $this->seedUser('admin', 'SEED_ADMIN_PASSWORD');
        $this->seedUser('tecnico', 'SEED_TECNICO_PASSWORD');
        $this->seedUser('consultor', 'SEED_CONSULTOR_PASSWORD');
    }

    private function seedUser(string $username, string $passwordEnvKey): void
    {
        $password = $this->resolvePassword($username, $passwordEnvKey);

        if ($password === null) {
            return;
        }

        $user = User::firstOrCreate(
            ['username' => $username],
            [
                'password' => Hash::make($password),
                'rol' => $username,
                'tema' => 'light',
            ]
        );

        $user->syncRoles([Role::findByName($username, 'web')]);
    }

    private function resolvePassword(string $username, string $envKey): ?string
    {
        $password = env($envKey);
        if (is_string($password) && $password !== '') {
            return $password;
        }

        if (app()->environment('production')) {
            $this->command?->warn("[$envKey] sin definir: usuario '{$username}' NO creado en producción.");

            return null;
        }

        $generated = Str::password(16);
        $this->command?->warn("Usuario '{$username}': $envKey sin definir, contraseña aleatoria generada: {$generated}");

        return $generated;
    }
}
