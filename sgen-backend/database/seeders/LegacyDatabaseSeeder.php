<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class LegacyDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $dumpPath = base_path('../sgen_db.sql');
        if (! file_exists($dumpPath)) {
            $this->command->error("Archivo de dump no encontrado en: $dumpPath");
            return;
        }

        $this->command->info("Cargando dump SQL desde: $dumpPath");
        $sql = file_get_contents($dumpPath);

        $tableOrder = [
            'categorias',
            'departamentos',
            'empleados',
            'usuarios',
            'equipos',
            'inventario_items',
            'inventario_ubicaciones',
            'inventario_movimientos',
            'soportes',
            'bitacora_acciones',
            'notificaciones',
            'sesiones_log',
        ];

        preg_match_all('/INSERT INTO `([^`]+)` \(([^)]+)\) VALUES\s*([\s\S]+?);/m', $sql, $matches, PREG_SET_ORDER);

        $statementsByTable = [];
        foreach ($matches as $m) {
            $table = $m[1];
            $cols = $m[2];
            $vals = $m[3];
            $statementsByTable[$table][] = ['cols' => $cols, 'vals' => $vals];
        }

        DB::statement("SET session_replication_role = 'replica';");

        foreach ($tableOrder as $table) {
            if (! isset($statementsByTable[$table])) {
                $this->command->line("  - Saltando '$table' (sin sentencias INSERT)");
                continue;
            }

            DB::table($table)->delete();
            $totalInserted = 0;

            foreach ($statementsByTable[$table] as $entry) {
                $cols = str_replace('`', '"', $entry['cols']);
                $vals = $entry['vals'];

                $vals = preg_replace("/\\\\\'/", "''", $vals);
                $vals = str_replace('\\"', '"', $vals);
                $vals = str_replace("\r\n", "\n", $vals);

                if ($table === 'categorias') {
                    $vals = preg_replace('/,\s*1\)/', ', true)', $vals);
                    $vals = preg_replace('/,\s*0\)/', ', false)', $vals);
                }

                if ($table === 'notificaciones') {
                    $vals = preg_replace('/,\s*1,\s*\'(\d{4}-)/', ', true, \'$1', $vals);
                    $vals = preg_replace('/,\s*0,\s*\'(\d{4}-)/', ', false, \'$1', $vals);
                }

                $pgSql = "INSERT INTO \"$table\" ($cols) VALUES $vals";
                DB::statement($pgSql);
            }

            try {
                DB::statement("SELECT setval(pg_get_serial_sequence('$table', 'id'), COALESCE(MAX(id), 1)) FROM \"$table\"");
            } catch (\Throwable $e) {
                // Sequence reset fallback
            }

            $count = DB::table($table)->count();
            $this->command->info("  ✔ Tabla '$table' poblada con $count registros.");
        }

        DB::statement("SET session_replication_role = 'origin';");

        // Sync Spatie Roles & standard users
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $tecnicoRole = Role::firstOrCreate(['name' => 'tecnico', 'guard_name' => 'web']);
        $consultorRole = Role::firstOrCreate(['name' => 'consultor', 'guard_name' => 'web']);

        $admin = User::find(1);
        if ($admin) {
            $admin->password = Hash::make('admin123');
            $admin->save();
            $admin->syncRoles([$adminRole]);
            $this->command->info("  ✔ Usuario 'admin' (ID: 1) configurado con clave 'admin123' (Rol: admin).");
        }

        $alexis = User::where('username', 'alexisd')->first();
        if ($alexis) {
            $alexis->password = Hash::make('tecnico123');
            $alexis->save();
            $alexis->syncRoles([$tecnicoRole]);
            $this->command->info("  ✔ Usuario 'alexisd' configurado con clave 'tecnico123' (Rol: tecnico).");
        }

        $alejandro = User::where('username', 'Alejandro Medina')->first();
        if ($alejandro) {
            $alejandro->password = Hash::make('admin123');
            $alejandro->save();
            $alejandro->syncRoles([$adminRole]);
            $this->command->info("  ✔ Usuario 'Alejandro Medina' configurado con clave 'admin123' (Rol: admin).");
        }

        $tecnico = User::firstOrCreate(
            ['username' => 'tecnico'],
            ['password' => Hash::make('tecnico123'), 'rol' => 'tecnico', 'tema' => 'light']
        );
        $tecnico->syncRoles([$tecnicoRole]);

        $consultor = User::firstOrCreate(
            ['username' => 'consultor'],
            ['password' => Hash::make('consultor123'), 'rol' => 'consultor', 'tema' => 'light']
        );
        $consultor->syncRoles([$consultorRole]);

        $this->command->info("  ✔ Usuarios de prueba 'tecnico' y 'consultor' verificados.");
    }
}
