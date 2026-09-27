<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Protección de la trazabilidad histórica (regla #43 extendida a la BD):
 *
 * Las cascadas de las tablas operativas convertían cualquier borrado en
 * destrucción de evidencia (eliminar equipo llevaba sus tickets y sus
 * órdenes; eliminar ítem llevaba su kardex; eliminar usuario sus consumos).
 * Se reescriben a RESTRICT/SET NULL donde la historia debe sobrevivir.
 *
 * NOTA: `soportes.equipo_id` y `mantenimientos.equipo_id` quedan CASCADE;
 * la barrera defensiva está en la aplicación (más adelante los casos de
 * uso bloquean eliminarlos con historia). El motor protege el inventario.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // El kardex jamás desaparece con el ítem. Borrado de ítem se rehuye
        // por diseño (los casos de uso verifican historia antes de eliminar).
        DB::statement(
            'ALTER TABLE inventario_movimientos
             DROP CONSTRAINT IF EXISTS inventario_movimientos_item_id_foreign'
        );
        DB::statement(
            'ALTER TABLE inventario_movimientos
             ADD CONSTRAINT inventario_movimientos_item_id_foreign
             FOREIGN KEY (item_id) REFERENCES inventario_items(id) ON DELETE RESTRICT'
        );

        // Nunca se rompe el patrimonio: los tickets/órdenes no se borran en
        // cascada al retirar el activo. La única vía correcta es la baja formal.
        DB::statement('ALTER TABLE soportes DROP CONSTRAINT IF EXISTS soportes_equipo_id_foreign');
        DB::statement(
            'ALTER TABLE soportes
             ADD CONSTRAINT soportes_equipo_id_foreign
             FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE RESTRICT'
        );
        DB::statement('ALTER TABLE mantenimientos DROP CONSTRAINT IF EXISTS mantenimientos_equipo_id_foreign');
        DB::statement(
            'ALTER TABLE mantenimientos
             ADD CONSTRAINT mantenimientos_equipo_id_foreign
             FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE RESTRICT'
        );

        // Un usuario/ítem/ticket desaparecido no puede llevarse los consumos.
        foreach (
            [
                ['inventario_consumos_usuario_id_foreign', 'usuario_id', 'usuarios'],
                ['inventario_consumos_item_id_foreign', 'item_id', 'inventario_items'],
            ] as [$fk, $col, $ref]
        ) {
            DB::statement("ALTER TABLE inventario_consumos DROP CONSTRAINT IF EXISTS {$fk}");
            DB::statement(
                "ALTER TABLE inventario_consumos ADD CONSTRAINT {$fk} FOREIGN KEY ({$col}) REFERENCES {$ref}(id) ON DELETE RESTRICT"
            );
        }

        // La ubicación del stock referencia a su ítem; el ítem nunca se va solo.
        DB::statement('ALTER TABLE inventario_ubicaciones DROP CONSTRAINT IF EXISTS inventario_ubicaciones_item_id_foreign');
        DB::statement(
            'ALTER TABLE inventario_ubicaciones
             ADD CONSTRAINT inventario_ubicaciones_item_id_foreign
             FOREIGN KEY (item_id) REFERENCES inventario_items(id) ON DELETE RESTRICT'
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (
            [
                ['inventario_movimientos', 'item_id', 'inventario_items'],
                ['soportes', 'equipo_id', 'equipos'],
                ['mantenimientos', 'equipo_id', 'equipos'],
                ['inventario_consumos', 'usuario_id', 'usuarios'],
                ['inventario_consumos', 'item_id', 'inventario_items'],
                ['inventario_ubicaciones', 'item_id', 'inventario_items'],
            ] as [$tabla, $col, $ref]
        ) {
            DB::statement("ALTER TABLE {$tabla} DROP CONSTRAINT IF EXISTS {$tabla}_{$col}_foreign");
            DB::statement(
                "ALTER TABLE {$tabla} ADD CONSTRAINT {$tabla}_{$col}_foreign
                 FOREIGN KEY ({$col}) REFERENCES {$ref}(id) ON DELETE CASCADE"
            );
        }
    }
};
