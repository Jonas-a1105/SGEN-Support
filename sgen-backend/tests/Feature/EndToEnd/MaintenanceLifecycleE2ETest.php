<?php

declare(strict_types=1);

namespace Tests\Feature\EndToEnd;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Prueba de Ciclo de Vida Completo E2E: Módulo de Mantenimiento (CMMS)
 *
 * Flujo:
 * 1. Crear orden de mantenimiento preventivo con checklist de tareas y frecuencia.
 * 2. Visualizar orden en el Dashboard y en la ficha de detalle.
 * 3. Completar mantenimiento registrando checklist evaluado, costos reales y observaciones.
 * 4. Verificar que el historial del equipo asociado refleja el mantenimiento completado.
 */
final class MaintenanceLifecycleE2ETest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;

    private int $equipmentId;

    private int $technicianId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['username' => 'admin_e2e_maint'],
            [
                'password' => bcrypt('secret123'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($this->adminUser);

        $deptId = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'DataCenter E2E',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->technicianId = (int) DB::table('empleados')->insertGetId([
            'cedula' => 'V-'.rand(10000000, 99999999),
            'nombre' => 'Gabriel',
            'apellido' => 'Soporte',
            'email' => 'gabriel.maint@sgen.com',
            'rol' => 'tecnico',
            'departamento_id' => $deptId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->equipmentId = (int) DB::table('equipos')->insertGetId([
            'codigo_inventario' => 'SRV-E2E-'.rand(1000, 9999),
            'numero_serie' => 'SN-SRV-'.uniqid(),
            'tipo' => 'Servidor',
            'marca' => 'HPE',
            'modelo' => 'ProLiant DL380 Gen10',
            'estado' => 'en_uso',
            'departamento_id' => $deptId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_complete_maintenance_lifecycle(): void
    {
        // 1. Crear Orden de Mantenimiento Preventivo
        $scheduledDate = now()->addDays(3)->format('Y-m-d H:i:s');
        $nextDate = now()->addMonths(6)->format('Y-m-d');

        $createPayload = [
            'equipo_id' => $this->equipmentId,
            'fecha' => $scheduledDate,
            'tipo_mantenimiento' => 'preventivo',
            'descripcion' => 'Mantenimiento Semestral de Servidor Principal',
            'frecuencia' => 'semestral',
            'proxima_fecha' => $nextDate,
            'tecnico_id' => $this->technicianId,
            'observaciones' => 'Revisar arreglo RAID 5 y pasta térmica',
            'duracion' => 120,
        ];

        $createResponse = $this->post('/mantenimientos', $createPayload);
        $createResponse->assertRedirect('/mantenimientos');

        $order = DB::table('mantenimientos')
            ->where('equipo_id', $this->equipmentId)
            ->where('tipo_mantenimiento', 'preventivo')
            ->first();

        $this->assertNotNull($order, 'La orden de mantenimiento debe crearse en base de datos.');
        $orderId = (int) $order->id;
        $this->assertSame('pendiente', $order->estado);

        // 2. Visualizar orden en el Dashboard y Ficha de Detalle
        $dashboardResponse = $this->get('/mantenimientos/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertInertia(fn (Assert $page) => $page
            ->component('Maintenance/Dashboard')
            ->has('kpis')
            ->has('proximos')
        );

        $detailResponse = $this->get("/mantenimientos/{$orderId}");
        $detailResponse->assertStatus(200);
        $detailResponse->assertInertia(fn (Assert $page) => $page
            ->component('Maintenance/Show')
            ->has('maintenance')
            ->where('maintenance.id', $orderId)
        );

        // 3. Completar mantenimiento
        $completePayload = [
            'costo' => 85.00,
            'observaciones' => 'Mantenimiento finalizado exitosamente. Limpieza total y RAID 5 en estado óptimo.',
            'checklist' => [
                ['tarea' => 'Limpieza de ventiladores', 'completada' => true],
                ['tarea' => 'Verificación de discos RAID', 'completada' => true],
                ['tarea' => 'Actualización de firmware iLO', 'completada' => true],
            ],
        ];

        $completeResponse = $this->post("/mantenimientos/{$orderId}/completar", $completePayload);
        $completeResponse->assertStatus(302);

        $completedOrder = DB::table('mantenimientos')->where('id', $orderId)->first();
        $this->assertSame('completado', $completedOrder->estado);
        $this->assertEquals(85.00, (float) $completedOrder->costo);
        $this->assertStringContainsString('Limpieza total y RAID 5', (string) $completedOrder->observaciones);

        // 4. Verificar que la ficha del equipo refleja el mantenimiento completado en su historial
        $equipmentResponse = $this->get("/equipos/{$this->equipmentId}");
        $equipmentResponse->assertStatus(200);
        $equipmentResponse->assertInertia(fn (Assert $page) => $page
            ->component('Equipment/Show')
            ->has('equipment.maintenances')
        );

        $equipmentData = DB::table('equipos')->where('id', $this->equipmentId)->first();
        $this->assertNotNull($equipmentData);
    }
}
