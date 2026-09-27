<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sesiones forenses completas: red y agente del acceso, y motivo de cierre
 * (manual / inactividad). Requisito del idle timeout y del módulo de
 * auditoría forense.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesiones_log', function (Blueprint $table) {
            if (! Schema::hasColumn('sesiones_log', 'ip')) {
                $table->string('ip', 45)->nullable()->after('username');
            }
            if (! Schema::hasColumn('sesiones_log', 'user_agent')) {
                $table->string('user_agent', 512)->nullable()->after('ip');
            }
            if (! Schema::hasColumn('sesiones_log', 'motivo_cierre')) {
                $table->string('motivo_cierre', 30)->nullable()->after('fecha_fin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sesiones_log', function (Blueprint $table) {
            foreach (['ip', 'user_agent', 'motivo_cierre'] as $column) {
                if (Schema::hasColumn('sesiones_log', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
