<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Datos mínimos y deterministas para las pruebas E2E (Playwright).
 * Crea roles, usuarios de prueba y un catálogo mínimo (departamento,
 * técnico, equipo) además de tickets para verificar el alcance por fila.
 * Jamás se ejecuta en producción.
 */
final class E2ESeeder extends Seeder
{
    public const ADMIN_USERNAME = 'e2e_admin';

    public const ADMIN_PASSWORD = 'E2e.Pass-2026*';

    public const OPERADOR_USERNAME = 'e2e_operador';

    public const OPERADOR_PASSWORD = 'E2e.Pass-2026*';

    public const TICKET_PROPIO = 'Ticket propio del operador E2E';

    public const TICKET_AJENO = 'Ticket ajeno creado por admin E2E';

    public const EQUIPO_SERIE = 'SN-E2E-001';

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('E2ESeeder no se ejecuta en producción.');

            return;
        }

        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(CoreCatalogSeeder::class);

        $admin = User::updateOrCreate(
            ['username' => self::ADMIN_USERNAME],
            ['password' => Hash::make(self::ADMIN_PASSWORD), 'rol' => 'admin', 'tema' => 'light']
        );
        $admin->syncRoles([Role::findByName('admin', 'web')]);

        $operador = User::updateOrCreate(
            ['username' => self::OPERADOR_USERNAME],
            ['password' => Hash::make(self::OPERADOR_PASSWORD), 'rol' => 'operador', 'tema' => 'light']
        );
        $operador->syncRoles([Role::findByName('operador', 'web')]);

        $departamentoId = (int) (DB::table('departamentos')->where('nombre', 'Soporte TI E2E')->value('id')
            ?? DB::table('departamentos')->insertGetId([
                'nombre' => 'Soporte TI E2E',
                'descripcion' => 'Departamento de pruebas E2E',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]));

        DB::table('empleados')->updateOrInsert(
            ['email' => 'tecnico.e2e@empresa.test'],
            [
                'nombre' => 'Técnico',
                'apellido' => 'E2E',
                'cedula' => 'V-99000001',
                'rol' => 'tecnico',
                'departamento_id' => $departamentoId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        $equipoId = (int) (DB::table('equipos')->where('numero_serie', self::EQUIPO_SERIE)->value('id')
            ?? DB::table('equipos')->insertGetId([
                'codigo_inventario' => 'EQ-E2E-001',
                'numero_serie' => self::EQUIPO_SERIE,
                'tipo' => 'computadora',
                'marca' => 'Dell',
                'modelo' => 'Latitude E2E',
                'estado' => 'disponible',
                'departamento_id' => $departamentoId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]));

        // Idempotencia de verdad: el estado debe seguir listo para operaciones E2E.
        DB::table('equipos')->where('id', $equipoId)->update(['estado' => 'disponible']);

        $this->seedTicket(self::TICKET_PROPIO, $operador->id, $equipoId);
        $this->seedTicket(self::TICKET_AJENO, $admin->id, $equipoId);
    }

    private function seedTicket(string $titulo, int $creadorId, int $equipoId): void
    {
        if (DB::table('soportes')->where('titulo', $titulo)->exists()) {
            return;
        }

        DB::table('soportes')->insert([
            'titulo' => $titulo,
            'descripcion' => 'Ticket determinista para verificación E2E del alcance por fila.',
            'equipo_id' => $equipoId,
            'estado' => 'pendiente',
            'prioridad' => 'media',
            'fecha' => Carbon::now(),
            'usuario_creacion_id' => $creadorId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
