<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Numeración legible y transaccional de tickets: TIC-AAAA-00001.
 *
 * - Tabla `correlativos`: contador por clave con lockForUpdate dentro de la
 *   misma transacción de creación — imposible duplicar aunque lleguen dos
 *   altas en el mismo instante.
 * - `soportes.codigo`: código único legible. Las filas históricas se
 *   rellenan conservando su año de creación y orden cronológico, y el
 *   contador queda listo para continuar la serie.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correlativos', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 60)->unique();
            $table->unsignedBigInteger('valor')->default(0);
            $table->timestamps();
        });

        Schema::table('soportes', function (Blueprint $table) {
            if (! Schema::hasColumn('soportes', 'codigo')) {
                $table->string('codigo', 25)->nullable()->after('id');
            }
        });

        // Backfill histórico: serie anual ordenada por id (pgSQL).
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                WITH rankeado AS (
                    SELECT id,
                           'TIC-' || EXTRACT(YEAR FROM fecha)::int
                                  || '-' || lpad(row_number() OVER (
                                        PARTITION BY EXTRACT(YEAR FROM fecha)
                                        ORDER BY id
                                    )::text, 5, '0') AS cod
                    FROM soportes
                )
                UPDATE soportes
                SET codigo = rankeado.cod
                FROM rankeado
                WHERE soportes.id = rankeado.id AND soportes.codigo IS NULL
            SQL);

            DB::statement(<<<'SQL'
                INSERT INTO correlativos (clave, valor, created_at, updated_at)
                SELECT 'ticket:' || EXTRACT(YEAR FROM fecha)::int, COUNT(*), NOW(), NOW()
                FROM soportes
                GROUP BY EXTRACT(YEAR FROM fecha)
                ON CONFLICT (clave) DO NOTHING
            SQL);
        }

        Schema::table('soportes', function (Blueprint $table) {
            $table->unique('codigo', 'soportes_codigo_unique');
        });
    }

    public function down(): void
    {
        Schema::table('soportes', function (Blueprint $table) {
            if (Schema::hasColumn('soportes', 'codigo')) {
                $table->dropUnique('soportes_codigo_unique');
                $table->dropColumn('codigo');
            }
        });

        Schema::dropIfExists('correlativos');
    }
};
