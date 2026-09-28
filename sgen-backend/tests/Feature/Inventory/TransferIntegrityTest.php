<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Inventory\Application\DTOs\TransferStockDTO;
use Modules\Inventory\Domain\Ports\ProductRepositoryInterface;
use Tests\TestCase;

/**
 * Regla #15/16 de integridad de inventario: transferencias reales
 * (origen ≠ destino, cantidad > 0, stock suficiente con lock, kardex con
 * referencia tipo+id) — y el motor lo impone aunque la app falle.
 */
final class TransferIntegrityTest extends TestCase
{
    use DatabaseTransactions;

    private int $itemId;

    private int $deptA;

    private int $deptB;

    private ProductRepositoryInterface $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->itemId = (int) DB::table('inventario_items')->insertGetId([
            'codigo' => 'TRF-'.uniqid(),
            'nombre' => 'Ítem transferencia',
            'categoria' => 'pruebas',
            'descripcion' => 'Fixture regla #15',
            'stock_actual' => 10,
            'stock_minimo' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $this->deptA = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Bodega A '.uniqid(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->deptB = (int) DB::table('departamentos')->insertGetId([
            'nombre' => 'Bodega B '.uniqid(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('inventario_ubicaciones')->insert([
            'item_id' => $this->itemId, 'departamento_id' => $this->deptA, 'cantidad' => 10,
        ]);

        $this->inventory = app(ProductRepositoryInterface::class);
    }

    private function transferir(int $origen, int $destino, int $cantidad): void
    {
        $this->inventory->transferStock(
            TransferStockDTO::fromArray([
                'item_id' => $this->itemId,
                'origen_id' => $origen,
                'destino_id' => $destino,
                'cantidad' => $cantidad,
            ]),
            $this->adminId()
        );
    }

    private function adminId(): int
    {
        return (int) User::firstOrCreate(
            ['username' => 'admin_transfer_'.uniqid()],
            ['password' => Hash::make('secret'), 'rol' => 'admin', 'tema' => 'light']
        )->id;
    }

    public function test_transferencia_con_origen_igual_a_destino_es_rechazada(): void
    {
        try {
            $this->transferir($this->deptA, $this->deptA, 1);
            $this->fail('Se esperaba DomainException: origen = destino.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('distintos', $e->getMessage());
        }
    }

    public function test_transferencia_con_stock_insuficiente_es_rechazada_bajo_lock(): void
    {
        try {
            $this->transferir($this->deptA, $this->deptB, 50);
            $this->fail('Se esperaba DomainException: stock insuficiente.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('insuficiente', mb_strtolower($e->getMessage()));
        }

        // El saldo por ubicación queda intacto (transacción abortada).
        $this->assertSame(10, (int) DB::table('inventario_ubicaciones')
            ->where('item_id', $this->itemId)->where('departamento_id', $this->deptA)->value('cantidad'));
    }

    public function test_transferencia_valida_mueve_saldo_y_no_pierde_nada(): void
    {
        $this->transferir($this->deptA, $this->deptB, 3);

        $this->assertSame(7, (int) DB::table('inventario_ubicaciones')
            ->where('item_id', $this->itemId)->where('departamento_id', $this->deptA)->value('cantidad'));
        $this->assertSame(3, (int) DB::table('inventario_ubicaciones')
            ->where('item_id', $this->itemId)->where('departamento_id', $this->deptB)->value('cantidad'));

        $this->assertDatabaseHas('inventario_movimientos', [
            'item_id' => $this->itemId,
            'tipo_movimiento' => 'TRANSFERENCIA',
            'cantidad' => 3,
        ]);
    }

    public function test_check_bd_rechaza_saldo_negativo_en_ubicacion(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('CHECK a motor, solo PostgreSQL.');
        }

        $this->expectException(QueryException::class);
        DB::table('inventario_ubicaciones')
            ->where('item_id', $this->itemId)->where('departamento_id', $this->deptA)
            ->update(['cantidad' => -1]);
    }
}
