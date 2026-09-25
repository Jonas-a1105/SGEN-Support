<?php

declare(strict_types=1);

namespace Tests\Feature\EndToEnd;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Prueba de Ciclo de Vida Completo E2E: Módulo de Equipos Tecnológicos
 *
 * Flujo:
 * 1. Alta de equipo tecnológico con especificaciones de hardware y garantía.
 * 2. Unicidad obligatoria: rechazo de código patrimonial duplicado.
 * 3. Asignación inicial a empleado y departamento A.
 * 4. Traslado seguro a departamento B validando departamento de origen.
 * 5. Rechazo estricto si el origen no coincide.
 * 6. Consulta de detalle de equipo (cálculo de garantía y contratos DTO).
 * 7. Baja / eliminación del activo.
 */
final class EquipmentLifecycleE2ETest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;

    private int $deptAId;

    private int $deptBId;

    private int $employeeId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['username' => 'admin_e2e_equip'],
            [
                'password' => bcrypt('secret123'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($this->adminUser);

        $this->deptAId = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Dpto Contabilidad E2E',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deptBId = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Dpto Auditoría E2E',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->employeeId = (int) DB::table('empleados')->insertGetId([
            'cedula' => 'V-'.rand(10000000, 99999999),
            'nombre' => 'Patricia',
            'apellido' => 'Sosa',
            'email' => 'patricia.e2e@sgen.com',
            'rol' => 'consultor',
            'departamento_id' => $this->deptAId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_complete_equipment_lifecycle(): void
    {
        $code = 'EQ-E2E-PC-'.rand(1000, 9999);
        $serial = 'SN-E2E-PC-'.uniqid();

        // 1. Alta de equipo tecnológico
        $payload = [
            'codigo_inventario' => $code,
            'numero_serie' => $serial,
            'tipo' => 'Computadora',
            'marca' => 'Dell',
            'modelo' => 'OptiPlex 7090 MT',
            'estado' => 'en_uso',
            'departamento_id' => $this->deptAId,
            'empleado_id' => $this->employeeId,
            'ubicacion_fisica' => 'Edificio Central - Piso 2 Of. 204',
            'procesador' => 'Intel Core i7-11700',
            'memoria_ram' => '16GB DDR4',
            'almacenamiento' => '512GB NVMe SSD',
            'sistema_operativo' => 'Windows 11 Pro',
            'direccion_ip' => '192.168.1.145',
            'fecha_compra' => now()->subMonths(6)->toDateString(),
            'garantia' => now()->addMonths(30)->toDateString(),
            'valor_compra' => 850.00,
            'proveedor' => 'Dell Direct Venezuela',
        ];

        $createResponse = $this->post('/equipos', $payload);
        $createResponse->assertRedirect('/equipos');

        $equipment = DB::table('equipos')->where('codigo_inventario', $code)->first();
        $this->assertNotNull($equipment, 'El equipo debe existir en la base de datos.');
        $equipmentId = (int) $equipment->id;
        $this->assertSame($this->deptAId, (int) $equipment->departamento_id);
        $this->assertSame($this->employeeId, (int) $equipment->empleado_id);

        // 2. Unicidad: rechazo de código duplicado
        $duplicateResponse = $this->post('/equipos', [
            'codigo_inventario' => $code,
            'tipo' => 'Computadora',
            'marca' => 'HP',
            'modelo' => 'EliteDesk',
        ]);
        $duplicateResponse->assertRedirect();
        $duplicateResponse->assertSessionHasErrors('codigo_inventario');

        // 3. Traslado seguro a Departamento B
        $transferResponse = $this->post('/equipos/trasladar', [
            'equipo_id' => $equipmentId,
            'departamento_origen_id' => $this->deptAId,
            'departamento_destino_id' => $this->deptBId,
            'motivo' => 'Transferencia permanente por cambio de funciones del usuario',
        ]);
        $transferResponse->assertRedirect();

        $transferredEquipment = DB::table('equipos')->where('id', $equipmentId)->first();
        $this->assertSame($this->deptBId, (int) $transferredEquipment->departamento_id);

        // 4. Rechazo estricto si el origen no coincide (equipo pertenece a B, pero se envía como origen A)
        $deptCId = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Dpto Logística E2E',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $wrongOriginResponse = $this->post('/equipos/trasladar', [
            'equipo_id' => $equipmentId,
            'departamento_origen_id' => $this->deptAId,
            'departamento_destino_id' => $deptCId,
        ]);
        $wrongOriginResponse->assertRedirect();
        $wrongOriginResponse->assertSessionHas('error');

        // 5. Consulta de Ficha de Detalle (Warranty calculation & DTO mapping)
        $detailResponse = $this->get("/equipos/{$equipmentId}");
        $detailResponse->assertStatus(200);
        $detailResponse->assertInertia(fn (Assert $page) => $page
            ->component('Equipment/Show')
            ->has('equipment')
            ->where('equipment.id', $equipmentId)
            ->where('equipment.inventoryCode', $code)
            ->where('equipment.departmentId', $this->deptBId)
            ->has('equipment.tickets')
            ->has('equipment.maintenances')
        );

        // 6. Eliminar activo
        $deleteResponse = $this->delete("/equipos/{$equipmentId}");
        $deleteResponse->assertRedirect('/equipos');

        $this->assertDatabaseMissing('equipos', ['id' => $equipmentId]);
    }
}
