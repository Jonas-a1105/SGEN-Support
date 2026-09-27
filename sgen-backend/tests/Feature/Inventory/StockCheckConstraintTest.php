<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Integridad de inventario a nivel motor: el CHECK impide stock negativo
 * incluso ante un UPDATE directo que saltee los casos de uso y su
 * lockForUpdate + InsufficientStockException.
 */
final class StockCheckConstraintTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('El CHECK de stock es específico de PostgreSQL.');
        }
    }

    private function crearItem(): int
    {
        return (int) DB::table('inventario_items')->insertGetId([
            'codigo' => 'CHK-'.uniqid(),
            'nombre' => 'Ítem prueba CHECK stock',
            'categoria' => 'pruebas',
            'descripcion' => 'Fixture para la constraint de stock no negativo',
            'stock_actual' => 3,
            'stock_minimo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_la_base_de_datos_rechaza_stock_negativo(): void
    {
        $itemId = $this->crearItem();

        $this->expectException(QueryException::class);
        // PostgreSQL aborta el UPDATE con violación del CHECK.
        DB::table('inventario_items')->where('id', $itemId)->update(['stock_actual' => -1]);
    }

    public function test_el_stock_puede_actualizarse_a_valores_validos(): void
    {
        $itemId = $this->crearItem();

        DB::table('inventario_items')->where('id', $itemId)->update(['stock_actual' => 0]);

        $this->assertSame(0, (int) DB::table('inventario_items')->where('id', $itemId)->value('stock_actual'));
    }
}
