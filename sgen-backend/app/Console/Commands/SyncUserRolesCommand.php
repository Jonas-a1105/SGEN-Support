<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;

/**
 * Repara/adopta el RBAC sobre instalaciones existentes: provisiona el
 * catálogo de roles y permisos y sincroniza el rol Spatie de cada
 * usuario desde su columna legacy `rol`.
 */
final class SyncUserRolesCommand extends Command
{
    protected $signature = 'sgen:sync-roles';

    protected $description = 'Sincroniza la columna usuarios.rol con los roles y permisos de Spatie';

    public function handle(RolesAndPermissionsSeeder $seeder): int
    {
        $seeder->run();

        $synced = 0;
        User::query()->orderBy('id')->chunkById(100, function ($users) use (&$synced): void {
            foreach ($users as $user) {
                $user->syncSpatieRoleFromColumn();
                $synced++;
            }
        });

        $this->info("RBAC sincronizado: {$synced} usuario(s) con rol asignado según catálogo.");

        return self::SUCCESS;
    }
}
