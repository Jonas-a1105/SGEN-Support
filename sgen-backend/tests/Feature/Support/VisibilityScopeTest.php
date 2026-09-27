<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Visibilidad por rol en los listados (visiblidad server-side, no UI):
 * operador solo ve su departamento; admin/técnico ven el mapa global;
 * consultor tiene pase global porque eso ES su rol (analítico).
 */
final class VisibilityScopeTest extends TestCase
{
    use DatabaseTransactions;

    private User $operador; // Empleado en DeptA

    private User $admin;

    private int $deptA;
    private int $deptB;
    private int $equipoA;
    private int $equipoB;
    private int $ticketA;
    private int $ticketB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deptA = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Área A '.uniqid(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->deptB = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Área B '.uniqid(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $empleadoA = (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Op', 'apellido' => 'A', 'email' => 'op.a.'.uniqid().'@empresa.com',
            'cedula' => 'V-'.random_int(10000000, 99999999), 'rol' => 'tecnico',
            'departamento_id' => $this->deptA, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->equipoA = (int) DB::table('equipos')->insertGetId([
            'codigo_inventario' => 'EQ-VIS-A-'.uniqid(), 'numero_serie' => 'SN-'.uniqid(),
            'tipo' => 'computadora', 'modelo' => 'X', 'estado' => 'disponible',
            'departamento_id' => $this->deptA, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->equipoB = (int) DB::table('equipos')->insertGetId([
            'codigo_inventario' => 'EQ-VIS-B-'.uniqid(), 'numero_serie' => 'SN-'.uniqid(),
            'tipo' => 'computadora', 'modelo' => 'X', 'estado' => 'disponible',
            'departamento_id' => $this->deptB, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_scope_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        );
        $this->operador = User::firstOrCreate(
            ['username' => 'operador_scope_'.uniqid()],
            ['password' => \Illuminate\Support\Facades\Hash::make('secret'), 'rol' => 'operador', 'empleado_id' => $empleadoA, 'tema' => 'light']
        );

        $this->ticketA = $this->crearTicket($this->equipoA, 'Ticket A');
        $this->ticketB = $this->crearTicket($this->equipoB, 'Ticket B');
    }

    private function crearTicket(int $equipo, string $titulo): int
    {
        return (int) DB::table('soportes')->insertGetId([
            'titulo' => $titulo.'. '.uniqid(),
            'descripcion' => 'prueba',
            'equipo_id' => $equipo,
            'estado' => 'pendiente',
            'prioridad' => 'media',
            'fecha' => Carbon::now(),
            'usuario_creacion_id' => $this->admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_operador_ve_activos_y_mantenimientos_solo_su_area(): void
    {
        $sesion = $this->actingAs($this->operador)->get('/equipos');
        $sesion->assertOk();
        $contenido = (string) $sesion->getContent();

        $this->assertStringContainsString('EQ-VIS-A', $contenido);
        $this->assertStringNotContainsString('EQ-VIS-B', $contenido);

        $this->actingAs($this->operador)->get('/mantenimientos')->assertOk();
    }

    public function test_admin_sigue_leyendo_el_mapa_global(): void
    {
        $sesion = $this->actingAs($this->admin)->get('/soportes');
        $sesion->assertOk();
        $this->assertStringContainsString('Ticket A', (string) $sesion->getContent());
        $this->assertStringContainsString('Ticket B', (string) $sesion->getContent());
    }

    public function test_operador_no_puede_leer_auditoria_ni_listado_global(): void
    {
        $this->actingAs($this->operador)->get('/auditoria')->assertForbidden();
        $this->actingAs($this->operador)->get('/usuarios')->assertForbidden();
    }
}
