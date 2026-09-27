<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Cantidades fraccionales (checklist #37): el kardex y los saldos aceptan
 * hasta 3 decimales (metros de cable, rollos fraccionados, litros). El motor
 * los conserva sin redondeos; los CHECK de positividad ya vigentes siguen
 * aplicando sobre el nuevo tipo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE inventario_items ALTER COLUMN stock_actual TYPE NUMERIC(15,3), ALTER COLUMN stock_minimo TYPE NUMERIC(15,3)');
        DB::statement('ALTER TABLE inventario_ubicaciones ALTER COLUMN cantidad TYPE NUMERIC(15,3)');
        DB::statement('ALTER TABLE inventario_movimientos ALTER COLUMN cantidad TYPE NUMERIC(15,3)');
        DB::statement('ALTER TABLE inventario_consumos ALTER COLUMN cantidad TYPE NUMERIC(15,3)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE inventario_items ALTER COLUMN stock_actual TYPE INTEGER USING stock_actual::int, ALTER COLUMN stock_minimo TYPE INTEGER USING stock_minimo::int');
        DB::statement('ALTER TABLE inventario_ubicaciones ALTER COLUMN cantidad TYPE INTEGER USING cantidad::int');
        DB::statement('ALTER TABLE inventario_movimientos ALTER COLUMN cantidad TYPE INTEGER USING cantidad::int');
        DB::statement('ALTER TABLE inventario_consumos ALTER COLUMN cantidad TYPE INTEGER USING cantidad::int');
    }
};
