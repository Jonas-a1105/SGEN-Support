<?php

declare(strict_types=1);

namespace Tests\Feature\Equipment;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class EquipmentControllerTest extends TestCase
{
    use DatabaseTransactions;

    private int $equipmentId;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate(
            ['username' => 'admin_equip_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($user);

        $this->equipmentId = (int) DB::table('equipos')->insertGetId([
            'codigo_inventario' => '00999',
            'numero_serie' => 'SN-TEST-'.uniqid(),
            'tipo' => 'Computadora',
            'marca' => 'Intel',
            'modelo' => 'N5095',
            'estado' => 'disponible',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_can_render_equipment_index_page_with_inertia(): void
    {
        $response = $this->get('/equipos');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Equipment/Index')
            ->has('kpis')
            ->has('equipos')
            ->has('options')
        );
    }

    public function test_can_render_equipment_show_page_with_inertia(): void
    {
        $response = $this->get("/equipos/{$this->equipmentId}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Equipment/Show')
            ->has('equipment')
            ->where('equipment.id', $this->equipmentId)
            ->has('equipment.tickets')
            ->has('equipment.maintenances')
            ->has('equipment.departamentos')
            ->has('equipment.empleados')
        );
    }

    public function test_can_show_single_equipment_json(): void
    {
        $response = $this->getJson("/equipos/{$this->equipmentId}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $this->equipmentId)
            ->assertJsonPath('inventoryCode', '00999');
    }

    public function test_can_create_equipment(): void
    {
        $payload = [
            'codigo_inventario' => '00888',
            'numero_serie' => 'SN-NEW-'.uniqid(),
            'tipo' => 'Impresora',
            'marca' => 'EPSON',
            'modelo' => 'L5590',
            'estado' => 'disponible',
        ];

        $response = $this->post('/equipos', $payload);

        $response->assertRedirect('/equipos');
        $this->assertDatabaseHas('equipos', [
            'codigo_inventario' => '00888',
            'tipo' => 'Impresora',
        ]);
    }

    public function test_can_update_equipment(): void
    {
        $payload = [
            'marca' => 'HP',
            'modelo' => 'LaserJet Pro',
            'estado' => 'en_uso',
        ];

        $response = $this->put("/equipos/{$this->equipmentId}", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('equipos', [
            'id' => $this->equipmentId,
            'marca' => 'HP',
            'modelo' => 'LaserJet Pro',
            'estado' => 'en_uso',
        ]);
    }

    public function test_can_delete_equipment(): void
    {
        $response = $this->delete("/equipos/{$this->equipmentId}");

        $response->assertRedirect('/equipos');
        $this->assertDatabaseMissing('equipos', [
            'id' => $this->equipmentId,
        ]);
    }

    public function test_can_transfer_equipment_between_departments(): void
    {
        $origenId = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Dpto. Origen Traslado Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $destinoId = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Dpto. Destino Traslado Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('equipos')
            ->where('id', $this->equipmentId)
            ->update(['departamento_id' => $origenId]);

        $response = $this->post('/equipos/trasladar', [
            'equipo_id' => $this->equipmentId,
            'departamento_origen_id' => $origenId,
            'departamento_destino_id' => $destinoId,
            'motivo' => 'Reorganización interna',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('equipos', [
            'id' => $this->equipmentId,
            'departamento_id' => $destinoId,
        ]);
    }

    public function test_transfer_equipment_rejects_wrong_origin(): void
    {
        $origenId = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Dpto. Orgen Inexistente Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $destinoId = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Dpto. Destino Inválido Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/equipos/trasladar', [
            'equipo_id' => $this->equipmentId,
            'departamento_origen_id' => $origenId,
            'departamento_destino_id' => $destinoId,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('equipos', [
            'id' => $this->equipmentId,
            'departamento_id' => null,
        ]);
    }

    public function test_can_register_equipment_with_unique_code(): void
    {
        $codigo = 'REG-'.strtoupper(uniqid());

        $response = $this->post('/equipos/registrar', [
            'codigo_inventario' => $codigo,
            'tipo' => 'Switch',
            'marca' => 'TP-Link',
            'modelo' => 'TL-SG1008D',
            'estado' => 'disponible',
            'numero_serie' => 'SN-REG-'.uniqid(),
        ]);

        $response->assertRedirect('/equipos');
        $this->assertDatabaseHas('equipos', [
            'codigo_inventario' => $codigo,
            'tipo' => 'Switch',
        ]);
    }

    public function test_register_equipment_rejects_duplicate_code(): void
    {
        $codigo = (string) DB::table('equipos')
            ->where('id', $this->equipmentId)
            ->value('codigo_inventario');

        $response = $this->post('/equipos/registrar', [
            'codigo_inventario' => $codigo,
            'tipo' => 'Switch',
            'marca' => 'TP-Link',
            'modelo' => 'TL-SG1008D',
            'estado' => 'disponible',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('codigo_inventario');
    }

    public function test_create_equipment_rejects_duplicate_code(): void
    {
        $codigo = (string) DB::table('equipos')
            ->where('id', $this->equipmentId)
            ->value('codigo_inventario');

        $response = $this->post('/equipos', [
            'codigo_inventario' => $codigo,
            'tipo' => 'Computadora',
            'marca' => 'Dell',
            'modelo' => 'Optiplex 7050',
            'estado' => 'disponible',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('codigo_inventario');
    }

    public function test_can_generate_custody_act_pdf(): void
    {
        $response = $this->get("/equipos/{$this->equipmentId}/acta-pdf");

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_can_export_equipment_excel(): void
    {
        $response = $this->get('/equipos/export/excel');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }
}
