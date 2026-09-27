<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Integridad de identificadores operativos (checklist 6.1 · #1, #3, #4, #28)
 * y formalización del estado `de_baja` de equipos (habilita la regla #30).
 *
 * Orden deliberado: saneamiento determinista de la data existente, luego
 * constraints parciales (índices UNIQUE vigentes) y la ampliación del enum.
 * Si quedaran duplicados realmente irreconciliables, la migración falla
 * con detalle en vez de crear integridad a medias.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // ── Saneamiento: cadena vacía NO es una IP ──────────────────────
        DB::table('equipos')
            ->whereNotNull('direccion_ip')
            ->whereRaw("btrim(direccion_ip) = ''")
            ->update(['direccion_ip' => null]);

        // ── Saneamiento: códigos patrimoniales duplicados del legado ────
        // Se conserva el registro más antiguo (menor id) y al resto se le
        // agrega sufijo -N (su id) para mantener unicidad sin perder datos.
        DB::statement(<<<'SQL'
            WITH d AS (
                SELECT id, codigo_inventario,
                       row_number() OVER (PARTITION BY codigo_inventario ORDER BY id) AS rn
                FROM equipos
                WHERE estado IS DISTINCT FROM 'de_baja'
            )
            UPDATE equipos e
            SET codigo_inventario = d.codigo_inventario || '-' || d.id
            FROM d
            WHERE e.id = d.id AND d.rn > 1
        SQL);

        // ── Re-validación: tras el saneamiento no puede quedar nada ─────
        $duplicados = fn (string $sql) => DB::select($sql);

        $faltantes = array_merge(
            $duplicados("SELECT 'ip' c, direccion_ip v FROM equipos WHERE direccion_ip IS NOT NULL AND estado IS DISTINCT FROM 'de_baja' GROUP BY direccion_ip HAVING COUNT(*) > 1"),
            $duplicados("SELECT 'codigo' c, codigo_inventario v FROM equipos WHERE estado IS DISTINCT FROM 'de_baja' GROUP BY codigo_inventario HAVING COUNT(*) > 1"),
            $duplicados("SELECT 'cedula' c, cedula v FROM empleados WHERE cedula IS NOT NULL AND deleted_at IS NULL GROUP BY cedula HAVING COUNT(*) > 1"),
            $duplicados("SELECT 'empleado_usuario' c, usuario_id::text v FROM empleados WHERE usuario_id IS NOT NULL AND deleted_at IS NULL GROUP BY usuario_id HAVING COUNT(*) > 1"),
            $duplicados("SELECT 'usuario_empleado' c, empleado_id::text v FROM usuarios WHERE empleado_id IS NOT NULL GROUP BY empleado_id HAVING COUNT(*) > 1"),
        );

        if ($faltantes !== []) {
            $detalle = collect($faltantes)->map(fn ($f) => "{$f->c}={$f->v}")->implode(', ');
            throw new RuntimeException("No se puede endurecer la integridad: duplicados vigentes sin resolver ({$detalle}).");
        }

        // ── Enum de estados de equipo: estados formales de fin de vida ──
        DB::statement('ALTER TABLE equipos DROP CONSTRAINT IF EXISTS equipos_estado_check');
        DB::statement(
            "ALTER TABLE equipos ADD CONSTRAINT equipos_estado_check CHECK (estado IN ("
            ."'nuevo','usado','en_uso','disponible','en_reserva','en_reparacion',"
            ."'fuera_de_servicio','prestado','de_baja','perdido'))"
        );

        // ── Constraints vigentes (parciales/condicionales) ──────────────
        // #1  IP única entre activos vigentes (baja reusa la IP).
        DB::statement(
            "CREATE UNIQUE INDEX IF NOT EXISTS equipos_ip_vigente_unique
             ON equipos (direccion_ip)
             WHERE direccion_ip IS NOT NULL AND estado IS DISTINCT FROM 'de_baja'"
        );

        // #3  Código patrimonial único vigente (la baja libera el código).
        DB::statement(
            "CREATE UNIQUE INDEX IF NOT EXISTS equipos_codigo_vigente_unique
             ON equipos (codigo_inventario)
             WHERE estado IS DISTINCT FROM 'de_baja'"
        );

        // #4  Cédula única de empleado vigente (soft-delete aware).
        DB::statement(
            "CREATE UNIQUE INDEX IF NOT EXISTS empleados_cedula_vigente_unique
             ON empleados (cedula)
             WHERE cedula IS NOT NULL AND deleted_at IS NULL"
        );

        // #28 Vínculo empleado↔usuario biunívoco, en ambas direcciones.
        DB::statement(
            "CREATE UNIQUE INDEX IF NOT EXISTS empleados_usuario_vigente_unique
             ON empleados (usuario_id)
             WHERE usuario_id IS NOT NULL AND deleted_at IS NULL"
        );
        DB::statement(
            "CREATE UNIQUE INDEX IF NOT EXISTS usuarios_empleado_unique
             ON usuarios (empleado_id)
             WHERE empleado_id IS NOT NULL"
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ([
            'usuarios_empleado_unique',
            'empleados_usuario_vigente_unique',
            'empleados_cedula_vigente_unique',
            'equipos_codigo_vigente_unique',
            'equipos_ip_vigente_unique',
        ] as $indice) {
            DB::statement("DROP INDEX IF EXISTS {$indice}");
        }

        DB::statement('ALTER TABLE equipos DROP CONSTRAINT IF EXISTS equipos_estado_check');
        DB::statement(
            "ALTER TABLE equipos ADD CONSTRAINT equipos_estado_check CHECK (estado IN ("
            ."'nuevo','usado','en_uso','fuera_de_servicio','en_reparacion','disponible','en_reserva'))"
        );
    }
};
