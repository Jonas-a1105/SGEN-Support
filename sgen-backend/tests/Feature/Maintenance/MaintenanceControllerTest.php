<?php

declare(strict_types=1);

namespace Tests\Feature\Maintenance;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class MaintenanceControllerTest extends TestCase
{
    use DatabaseTransactions;

    private int $equipmentId;
    private int $technicianId;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate(
            ['username' => 'admin_maint_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($user);

        // Crear departamento de prueba si no existe
        $deptId = DB::table('departamentos')->insertGetId([
            'nombre' => 'Dpto Test Maint ' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear equipo de prueba
        $this->equipmentId = (int) DB::table('equipos')->insertGetId([
            'codigo_inventario' => 'EQ-TEST-' . rand(1000, 9999),
            'numero_serie' => 'SN-TEST-' . uniqid(),
            'tipo' => 'computadora',
            'modelo' => 'Dell Optiplex 7090',
            'departamento_id' => $deptId,
            'estado' => 'disponible',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear técnico de prueba
        $this->technicianId = (int) DB::table('empleados')->insertGetId([
            'nombre' => 'Tecnico',
            'apellido' => 'Test',
            'email' => 'tecnico.test' . rand(100, 999) . '@sgen.test',
            'rol' => 'tecnico',
            'departamento_id' => $deptId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_can_render_maintenance_index_page_with_inertia(): void
    {
        $response = $this->get('/mantenimientos');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Maintenance/Index')
            ->has('mantenimientos')
            ->has('kpis')
            ->has('filters')
        );
    }

    public function test_can_render_maintenance_create_page_with_inertia(): void
    {
        $response = $this->get('/mantenimientos/crear');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Maintenance/Create')
            ->has('equipments')
            ->has('technicians')
            ->has('types')
            ->has('frequencies')
        );
    }

    public function test_can_create_maintenance_order(): void
    {
        $payload = [
            'equipo_id' => $this->equipmentId,
            'fecha' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'tipo_mantenimiento' => 'preventivo',
            'descripcion' => 'Limpieza preventiva y actualización de firmware',
            'frecuencia' => 'mensual',
            'proxima_fecha' => now()->addMonth()->format('Y-m-d'),
            'costo' => 45.50,
            'tecnico_id' => $this->technicianId,
            'observaciones' => 'Checklist: [X] Limpiar polvo; [ ] Actualizar BIOS',
            'duracion' => 60,
        ];

        $response = $this->post('/mantenimientos', $payload);

        $response->assertRedirect('/mantenimientos');
        $this->assertDatabaseHas('mantenimientos', [
            'equipo_id' => $this->equipmentId,
            'tipo_mantenimiento' => 'preventivo',
            'descripcion' => 'Limpieza preventiva y actualización de firmware',
        ]);
    }

    public function test_can_complete_maintenance(): void
    {
        $maintId = DB::table('mantenimientos')->insertGetId([
            'equipo_id' => $this->equipmentId,
            'fecha' => now()->toDateString(),
            'tipo_mantenimiento' => 'correctivo',
            'estado' => 'en_proceso',
            'descripcion' => 'Cambio de fuente de poder',
            'frecuencia' => 'unica',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post("/mantenimientos/{$maintId}/completar", [
            'observaciones' => 'Completado con éxito, fuente reemplazada.',
            'costo' => 35.00,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('mantenimientos', [
            'id' => $maintId,
            'estado' => 'completado',
        ]);
    }

    public function test_can_render_maintenance_dashboard(): void
    {
        $response = $this->get('/mantenimientos/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Maintenance/Dashboard')
            ->has('kpis')
            ->has('proximos')
        );
    }
}
