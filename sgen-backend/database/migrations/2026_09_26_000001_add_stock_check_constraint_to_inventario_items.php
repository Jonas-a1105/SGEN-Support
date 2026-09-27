<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Integridad FÍSICA del inventario a nivel motor de base de datos.
 *
 * Los casos de uso ya descuentan stock dentro de una transacción con
 * lockForUpdate y lanzan InsufficientStockException; este CHECK es la
 * última línea de defensa ante cualquier UPDATE manual, importación o
 * bug futuro de concurrencia: el stock jamás puede quedar negativo.
 *
 * Específico de PostgreSQL (driver real del proyecto); en otros drivers
 * la migración es un no-op controlado.
 */
return new class extends Migration
{
    private const CONSTRAINT = 'chk_inventario_items_stock_actual_no_negativo';

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $violaciones = (int) DB::table('inventario_items')
            ->where('stock_actual', '<', 0)
            ->count();

        if ($violaciones > 0) {
            throw new RuntimeException(
                "No se puede crear el CHECK de stock: existen {$violaciones} ítem(s) con stock_actual negativo. "
                .'Corrige el inventario físico antes de migrar.'
            );
        }

        DB::statement(
            'ALTER TABLE inventario_items ADD CONSTRAINT '.self::CONSTRAINT.' CHECK (stock_actual >= 0)'
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE inventario_items DROP CONSTRAINT IF EXISTS '.self::CONSTRAINT);
    }
};
