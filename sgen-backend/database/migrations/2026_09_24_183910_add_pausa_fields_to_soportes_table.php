<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Habilita pausas con motivo en tickets (PASO 5 del flujo):
 * - motivo_pausa: razón obligatoria al pausar (espera cliente, RMA, etc.)
 * - tiempo_pausado_minutos: acumulado de pausas para recálculo de SLA
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soportes', function (Blueprint $table) {
            $table->string('motivo_pausa', 500)->nullable()->after('estado');
            $table->integer('tiempo_pausado_minutos')->default(0)->after('motivo_pausa');
        });
    }

    public function down(): void
    {
        Schema::table('soportes', function (Blueprint $table) {
            $table->dropColumn(['motivo_pausa', 'tiempo_pausado_minutos']);
        });
    }
};
