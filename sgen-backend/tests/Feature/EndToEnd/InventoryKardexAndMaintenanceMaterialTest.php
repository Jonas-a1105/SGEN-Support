<?php

declare(strict_types=1);

namespace Tests\Feature\EndToEnd;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\EloquentProductModel;
use Tests\TestCase;

final class InventoryKardexAndMaintenanceMaterialTest extends TestCase
{
    use DatabaseTransactions;

    private int $userId;

    private int $equipmentId;

    private int $technicianId;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate(
            ['username' => 'admin_tester'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->userId = (int) $user->id;
        $this->actingAs($user);

        $deptId = DB::table('departamentos')->insertGetId([
            'nombre' => 'Mantenimiento Test '.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->technicianId = DB::table('empleados')->insertGetId([
            'nombre' => 'Carlos',
            'apellido' => 'Mendoza',
            'departamento_id' => $deptId,
            'email' => 'carlos.mendoza.'.uniqid().'@example.com',
            'rol' => 'tecnico',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->equipmentId = DB::table('equipos')->insertGetId([
            'codigo_inventario' => 'EQ-KARDEX-'.strtoupper(uniqid()),
            'tipo' => 'laptop',
            'marca' => 'Lenovo',
            'modelo' => 'ThinkPad T14',
            'numero_serie' => 'SN-'.strtoupper(uniqid()),
            'estado' => 'disponible',
            'departamento_id' => $deptId,
            'empleado_id' => $this->technicianId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_inventory_show_page_includes_kardex_movements_history(): void
    {
        $product = EloquentProductModel::create([
            'codigo' => 'RAM-DDR4-'.strtoupper(uniqid()),
            'nombre' => 'Memoria RAM 16GB DDR4 Kingston',
            'categoria' => 'Componentes',
            'stock_actual' => 20,
            'stock_minimo' => 5,
            'valor_compra' => 45.0,
            'unidad_medida' => 'uds',
        ]);

        DB::table('inventario_movimientos')->insert([
            'item_id' => $product->id,
            'usuario_id' => $this->userId,
            'tipo_movimiento' => 'ENTRADA',
            'cantidad' => 20,
            'motivo' => 'Compra inicial a proveedor',
            'fecha' => now(),
        ]);

        $response = $this->get("/inventario/{$product->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Inventory/Show')
            ->has('product')
            ->where('product.id', $product->id)
            ->where('product.name', 'Memoria RAM 16GB DDR4 Kingston')
            ->has('movements', 1)
            ->where('movements.0.tipo_movimiento', 'ENTRADA')
            ->where('movements.0.cantidad', 20)
        );
    }

    public function test_add_material_to_maintenance_order_deducts_stock_and_records_kardex(): void
    {
        $product = EloquentProductModel::create([
            'codigo' => 'SSD-1TB-'.strtoupper(uniqid()),
            'nombre' => 'Disco SSD NVMe 1TB Samsung',
            'categoria' => 'Almacenamiento',
            'stock_actual' => 10,
            'stock_minimo' => 2,
            'valor_compra' => 85.0,
            'unidad_medida' => 'uds',
        ]);

        $maintId = DB::table('mantenimientos')->insertGetId([
            'equipo_id' => $this->equipmentId,
            'fecha' => now()->toDateString(),
            'tipo_mantenimiento' => 'correctivo',
            'estado' => 'en_proceso',
            'descripcion' => 'Reemplazo de disco dañado',
            'frecuencia' => 'unica',
            'tecnico_id' => $this->technicianId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post("/mantenimientos/{$maintId}/materiales", [
            'item_id' => $product->id,
            'cantidad' => 3,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Verify stock deducted
        $this->assertDatabaseHas('inventario_items', [
            'id' => $product->id,
            'stock_actual' => 7,
        ]);

        // Verify material recorded in maintenance
        $this->assertDatabaseHas('mantenimiento_materiales', [
            'mantenimiento_id' => $maintId,
            'item_id' => $product->id,
            'cantidad' => 3,
        ]);

        // Verify Kardex movement generated
        $this->assertDatabaseHas('inventario_movimientos', [
            'item_id' => $product->id,
            'tipo_movimiento' => 'CONSUMO',
            'cantidad' => 3,
            'referencia_id' => $maintId,
        ]);

        // Verify maintenance show view returns materiales list
        $showResponse = $this->get("/mantenimientos/{$maintId}");
        $showResponse->assertStatus(200);
        $showResponse->assertInertia(fn (Assert $page) => $page
            ->component('Maintenance/Show')
            ->has('materiales', 1)
            ->where('materiales.0.item_id', $product->id)
            ->where('materiales.0.cantidad', 3)
            ->where('materiales.0.costo_unitario', fn ($val) => (float) $val === 85.0)
            ->where('materiales.0.costo_total', fn ($val) => (float) $val === 255.0)
        );
    }

    public function test_cannot_consume_more_material_than_current_inventory_stock(): void
    {
        $product = EloquentProductModel::create([
            'codigo' => 'BATT-T14-'.strtoupper(uniqid()),
            'nombre' => 'Batería Interna Lenovo T14',
            'categoria' => 'Repuestos',
            'stock_actual' => 2,
            'stock_minimo' => 1,
            'valor_compra' => 60.0,
            'unidad_medida' => 'uds',
        ]);

        $maintId = DB::table('mantenimientos')->insertGetId([
            'equipo_id' => $this->equipmentId,
            'fecha' => now()->toDateString(),
            'tipo_mantenimiento' => 'correctivo',
            'estado' => 'en_proceso',
            'descripcion' => 'Cambio de batería',
            'frecuencia' => 'unica',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post("/mantenimientos/{$maintId}/materiales", [
            'item_id' => $product->id,
            'cantidad' => 5, // Exceeds available 2
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        // Stock must remain unchanged
        $this->assertDatabaseHas('inventario_items', [
            'id' => $product->id,
            'stock_actual' => 2,
        ]);
    }
}
