<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Datos mínimos y deterministas para las pruebas E2E (Playwright).
 * Solo crea un usuario administrador de prueba con credencial fija de entorno
 * controlado. Jamás se ejecuta en producción.
 */
final class E2ESeeder extends Seeder
{
    public const USERNAME = 'e2e_admin';

    public const PASSWORD = 'E2e.Pass-2026*';

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('E2ESeeder no se ejecuta en producción.');

            return;
        }

        $this->call(RolesAndPermissionsSeeder::class);

        $user = User::updateOrCreate(
            ['username' => self::USERNAME],
            [
                'password' => Hash::make(self::PASSWORD),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );

        $user->syncRoles([Role::findByName('admin', 'web')]);
    }
}
