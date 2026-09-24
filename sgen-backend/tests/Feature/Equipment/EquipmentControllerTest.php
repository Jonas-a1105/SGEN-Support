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
            'numero_serie' => 'SN-TEST-' . uniqid(),
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
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Equipment/Index')
            ->has('kpis')
            ->has('equipos')
            ->has('options')
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
            'numero_serie' => 'SN-NEW-' . uniqid(),
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
}
