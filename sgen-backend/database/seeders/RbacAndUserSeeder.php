<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RbacAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear roles de Spatie Permission
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $tecnicoRole = Role::firstOrCreate(['name' => 'tecnico', 'guard_name' => 'web']);
        $consultorRole = Role::firstOrCreate(['name' => 'consultor', 'guard_name' => 'web']);

        // 2. Crear o actualizar usuarios institucionales base
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'password' => Hash::make('admin123'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $admin->syncRoles([$adminRole]);

        $tecnico = User::firstOrCreate(
            ['username' => 'tecnico'],
            [
                'password' => Hash::make('tecnico123'),
                'rol' => 'tecnico',
                'tema' => 'light',
            ]
        );
        $tecnico->syncRoles([$tecnicoRole]);

        $consultor = User::firstOrCreate(
            ['username' => 'consultor'],
            [
                'password' => Hash::make('consultor123'),
                'rol' => 'consultor',
                'tema' => 'light',
            ]
        );
        $consultor->syncRoles([$consultorRole]);
    }
}
