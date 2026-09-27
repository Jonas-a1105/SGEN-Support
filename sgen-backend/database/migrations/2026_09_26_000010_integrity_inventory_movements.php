<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Integridad operativa de inventario (checklist 6.1 · #15 y #16):
 *
 * - Stock por ubicación jamás negativo (el CHECK de `inventario_items`
 *   protege el global; este protege el saldo desagregado donde ocurre
 *   la transferencia).
 * - Movimientos: cantidad siempre positiva; en TRANSFERENCIA el origen y
 *   el destino deben diferir; la referencia lleva SIEMPRE tipo+id juntos
 *   (trazabilidad estructural del kardex).
 * - Consumos: cantidad positiva.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(
            'ALTER TABLE inventario_ubicaciones ADD CONSTRAINT chk_ubicaciones_cantidad_no_negativa CHECK (cantidad >= 0)'
        );

        // #15: referencia fuerte del kardex — tipo de entidad enlazada.
        DB::statement(
            'ALTER TABLE inventario_movimientos ADD COLUMN IF NOT EXISTS referencia_tipo VARCHAR(30) NULL'
        );

        DB::statement('ALTER TABLE inventario_movimientos DROP CONSTRAINT IF EXISTS chk_movimientos_cantidad_positiva');
        DB::statement('ALTER TABLE inventario_movimientos ADD CONSTRAINT chk_movimientos_cantidad_positiva CHECK (cantidad > 0)');

        DB::statement('ALTER TABLE inventario_consumos DROP CONSTRAINT IF EXISTS chk_consumos_cantidad_positiva');
        DB::statement('ALTER TABLE inventario_consumos ADD CONSTRAINT chk_consumos_cantidad_positiva CHECK (cantidad > 0)');

        // Saneamiento per-semântico del inventario (referencia_tipo) ANTES del
        // CHECK de pares: nada queda huérfano de tipo en datos legados.
        DB::statement(
            "UPDATE inventario_movimientos im
             SET referencia_tipo = 'soporte'
             WHERE im.referencia_id IS NOT NULL AND im.referencia_tipo IS NULL
               AND EXISTS (SELECT 1 FROM soportes s WHERE s.id = im.referencia_id)"
        );
        DB::statement(
            "UPDATE inventario_movimientos im
             SET referencia_tipo = 'mantenimiento'
             WHERE im.referencia_id IS NOT NULL AND im.referencia_tipo IS NULL
               AND EXISTS (SELECT 1 FROM mantenimientos m WHERE m.id = im.referencia_id)"
        );
        // Resto: movimiento con referencia procedente de otro mecanismo; se
        // clasifica como kardex en lugar de romper la migración.
        DB::statement(
            "UPDATE inventario_movimientos
             SET referencia_tipo = 'kardex'
             WHERE referencia_id IS NOT NULL AND referencia_tipo IS NULL"
        );

        // Regla #15 aplicada a datos históricos: una "transferencia" vacía
        // (mismo origen que destino) es un ajuste administrativo, no una
        // transferencia. Recl001sificado sin perder el testimonio.
        DB::statement(
            "UPDATE inventario_movimientos
             SET tipo_movimiento = 'AJUSTE',
                 motivo = COALESCE(motivo, '') || ' [legado: origen=destino]'
             WHERE tipo_movimiento = 'TRANSFERENCIA'
               AND origen_departamento_id IS NOT NULL
               AND origen_departamento_id = destino_departamento_id"
        );

        DB::statement('ALTER TABLE inventario_movimientos DROP CONSTRAINT IF EXISTS chk_movimientos_referencia_pares');
        DB::statement(
            'ALTER TABLE inventario_movimientos ADD CONSTRAINT chk_movimientos_referencia_pares
             CHECK ((referencia_id IS NULL) = (referencia_tipo IS NULL))'
        );

        DB::statement('ALTER TABLE inventario_movimientos DROP CONSTRAINT IF EXISTS chk_movimientos_origen_distinto_destino');
        DB::statement(
            "ALTER TABLE inventario_movimientos ADD CONSTRAINT chk_movimientos_origen_distinto_destino
             CHECK (tipo_movimiento <> 'TRANSFERENCIA' OR origen_departamento_id IS NULL OR origen_departamento_id <> destino_departamento_id)"
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE inventario_consumos DROP CONSTRAINT IF EXISTS chk_consumos_cantidad_positiva');
        DB::statement('ALTER TABLE inventario_movimientos DROP CONSTRAINT IF EXISTS chk_movimientos_origen_distinto_destino');
        DB::statement('ALTER TABLE inventario_movimientos DROP CONSTRAINT IF EXISTS chk_movimientos_referencia_pares');
        DB::statement('ALTER TABLE inventario_movimientos DROP CONSTRAINT IF EXISTS chk_movimientos_cantidad_positiva');
        DB::statement('ALTER TABLE inventario_movimientos DROP COLUMN IF EXISTS referencia_tipo');
        DB::statement('ALTER TABLE inventario_ubicaciones DROP CONSTRAINT IF EXISTS chk_ubicaciones_cantidad_no_negativa');
    }
};
