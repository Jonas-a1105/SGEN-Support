<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Infrastructure\Inventory\Persistence\Eloquent\Models\EloquentProductModel;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class InventoryControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate(
            ['username' => 'admin_test'],
            [
                'password' => bcrypt('secret'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($user);
    }

    public function test_can_render_inventory_index_page_with_inertia(): void
    {
        $response = $this->get('/inventario');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Inventory/Index')
            ->has('products')
            ->has('pagination')
            ->has('kpis')
            ->has('filters')
        );
    }

    public function test_can_create_product_via_form_request_and_use_case(): void
    {
        $rawSku = 'TEST-SKU-'.uniqid();
        $payload = [
            'sku' => $rawSku,
            'name' => 'Monitor Dell 27"',
            'category' => 'Pantallas',
            'initial_stock' => 15,
            'minimum_stock' => 3,
            'purchase_price' => 189.99,
            'description' => 'Monitor IPS 144Hz',
            'location' => 'Bodega Central',
        ];

        $response = $this->post('/inventario', $payload);

        $response->assertRedirect('/inventario');
        $this->assertDatabaseHas('inventario_items', [
            'codigo' => strtoupper($rawSku),
            'nombre' => $payload['name'],
            'stock_actual' => 15,
        ]);
    }

    public function test_can_adjust_product_stock_and_record_movement(): void
    {
        $product = EloquentProductModel::create([
            'codigo' => 'ADJ-SKU-'.strtoupper(uniqid()),
            'nombre' => 'Mouse USB',
            'categoria' => 'Periféricos',
            'stock_actual' => 10,
            'stock_minimo' => 2,
            'valor_compra' => 5.0,
        ]);

        $response = $this->post('/inventario/ajustar', [
            'product_id' => $product->id,
            'type' => 'SALIDA',
            'quantity' => 4,
            'reason' => 'Prueba de ajuste de salida',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('inventario_items', [
            'id' => $product->id,
            'stock_actual' => 6,
        ]);

        $this->assertDatabaseHas('inventario_movimientos', [
            'item_id' => $product->id,
            'tipo_movimiento' => 'SALIDA',
            'cantidad' => 4,
            'motivo' => 'Prueba de ajuste de salida',
        ]);
    }

    public function test_can_render_product_show_page(): void
    {
        $product = EloquentProductModel::create([
            'codigo' => 'SHOW-SKU-'.strtoupper(uniqid()),
            'nombre' => 'Router Cisco',
            'categoria' => 'Redes',
            'stock_actual' => 8,
            'stock_minimo' => 2,
            'valor_compra' => 120.0,
        ]);

        $response = $this->get("/inventario/{$product->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Inventory/Show')
            ->has('product')
            ->where('product.id', $product->id)
            ->where('product.name', 'Router Cisco')
            ->has('movements')
        );
    }

    public function test_low_stock_adjustment_triggers_in_app_notification(): void
    {
        $product = EloquentProductModel::create([
            'codigo' => 'CRIT-SKU-'.strtoupper(uniqid()),
            'nombre' => 'Cable HDMI 2.1',
            'categoria' => 'Cables',
            'stock_actual' => 5,
            'stock_minimo' => 4,
            'valor_compra' => 8.5,
        ]);

        // Ajustar salida de 3 unidades para dejarlo en 2 (menor al mínimo 4)
        $response = $this->post('/inventario/ajustar', [
            'product_id' => $product->id,
            'type' => 'SALIDA',
            'quantity' => 3,
            'reason' => 'Consumo de cables en sala de conferencias',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('notificaciones', [
            'tipo' => 'inventario_bajo_stock',
            'titulo' => 'Alerta de Stock Crítico',
        ]);
    }

    public function test_can_transfer_stock_between_departments(): void
    {
        $product = EloquentProductModel::create([
            'codigo' => 'TRF-SKU-'.strtoupper(uniqid()),
            'nombre' => 'Teclado Mecánico',
            'categoria' => 'Periféricos',
            'stock_actual' => 20,
            'stock_minimo' => 2,
            'valor_compra' => 45.0,
        ]);

        $dept1 = (int) \Illuminate\Support\Facades\DB::table('departamentos')->insertGetId([
            'nombre' => 'Almacén Central ' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dept2 = (int) \Illuminate\Support\Facades\DB::table('departamentos')->insertGetId([
            'nombre' => 'Taller TI ' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/inventario/transferir', [
            'item_id' => $product->id,
            'origen_id' => $dept1,
            'destino_id' => $dept2,
            'cantidad' => 5,
            'motivo' => 'Envío de stock a taller técnico',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventario_movimientos', [
            'item_id' => $product->id,
            'origen_departamento_id' => $dept1,
            'destino_departamento_id' => $dept2,
            'tipo_movimiento' => 'TRANSFERENCIA',
            'cantidad' => 5,
        ]);
    }
}
