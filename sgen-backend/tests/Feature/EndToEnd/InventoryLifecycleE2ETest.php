<?php

declare(strict_types=1);

namespace Tests\Feature\EndToEnd;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Prueba de Ciclo de Vida Completo E2E: Módulo de Inventario y Consumibles
 *
 * Flujo:
 * 1. Registro de nuevo producto / insumo con SKU único, stock inicial y stock mínimo.
 * 2. Visualización en el catálogo general con métricas KPI.
 * 3. Ajuste manual de stock hacia la baja (SALIDA) por merma o entrega a departamentos.
 * 4. Detección automática del umbral de stock bajo cuando stock_actual < stock_minimo.
 * 5. Transferencia de stock entre ubicaciones/departamentos.
 * 6. Consulta de la ficha de detalle del producto y kardex de movimientos.
 */
final class InventoryLifecycleE2ETest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;

    private int $deptDestinoId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['username' => 'admin_e2e_inv'],
            [
                'password' => bcrypt('secret123'),
                'rol' => 'admin',
                'tema' => 'light',
            ]
        );
        $this->actingAs($this->adminUser);

        $this->deptDestinoId = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Dpto Logística E2E Inv',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_complete_inventory_lifecycle(): void
    {
        $rawSku = 'TONER-HP-E2E-'.rand(1000, 9999);

        // 1. Registro de nuevo producto
        $createPayload = [
            'sku' => $rawSku,
            'name' => 'Tóner HP LaserJet Original 85A',
            'category' => 'Consumibles',
            'initial_stock' => 10,
            'minimum_stock' => 4,
            'purchase_price' => 65.50,
            'description' => 'Cartucho de tóner monocromático de alto rendimiento',
            'location' => 'Almacén General Estante B-2',
        ];

        $createResponse = $this->post('/inventario', $createPayload);
        $createResponse->assertRedirect('/inventario');

        $product = DB::table('inventario_items')->where('codigo', strtoupper($rawSku))->first();
        $this->assertNotNull($product, 'El producto debe registrarse en inventario_items.');
        $productId = (int) $product->id;
        $this->assertSame(10, (int) $product->stock_actual);
        $this->assertSame(4, (int) $product->stock_minimo);

        // 2. Visualización en catálogo general
        $indexResponse = $this->get('/inventario');
        $indexResponse->assertStatus(200);
        $indexResponse->assertInertia(fn (Assert $page) => $page
            ->component('Inventory/Index')
            ->has('products')
            ->has('kpis')
        );

        // 3. Ajuste de stock (SALIDA masiva de 7 unidades: 10 - 7 = 3)
        $adjustResponse = $this->post('/inventario/ajustar', [
            'product_id' => $productId,
            'type' => 'SALIDA',
            'quantity' => 7,
            'reason' => 'Despacho masivo extraordinario a sedes remotas',
        ]);
        $adjustResponse->assertStatus(302);

        $updatedProduct = DB::table('inventario_items')->where('id', $productId)->first();
        $this->assertSame(3, (int) $updatedProduct->stock_actual);

        // 4. Verificación de umbral de stock bajo (3 < 4)
        $this->assertTrue((int) $updatedProduct->stock_actual < (int) $updatedProduct->stock_minimo);

        // 5. Transferencia de stock a otra ubicación
        $transferResponse = $this->post('/inventario/transferir', [
            'item_id' => $productId,
            'origen_id' => null,
            'destino_id' => $this->deptDestinoId,
            'cantidad' => 1,
            'motivo' => 'Transferencia de reserva a contingencia',
        ]);
        $transferResponse->assertRedirect();

        // 6. Consulta de ficha de detalle
        $showResponse = $this->get("/inventario/{$productId}");
        $showResponse->assertStatus(200);
        $showResponse->assertInertia(fn (Assert $page) => $page
            ->component('Inventory/Show')
            ->has('product')
            ->where('product.id', $productId)
            ->where('product.name', 'Tóner HP LaserJet Original 85A')
        );
    }
}
