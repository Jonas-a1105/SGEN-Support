<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Baja patrimonial formal (Módulo 11 · checklist #43-familia):
 * un equipo no desaparece — se retira con motivo legal, responsable,
 * valor de recuperación, fecha y acta firmada (hash verificable).
 * El historial operativo se conserva íntegro para auditoría.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            if (! Schema::hasColumn('equipos', 'motivo_baja')) {
                $table->string('motivo_baja', 60)->nullable()->comment('robo|obsolescencia|donacion|venta|desecho');
            }
            if (! Schema::hasColumn('equipos', 'fecha_baja')) {
                $table->timestamp('fecha_baja')->nullable();
            }
            if (! Schema::hasColumn('equipos', 'valor_recuperacion')) {
                $table->decimal('valor_recuperacion', 15, 2)->nullable();
            }
            if (! Schema::hasColumn('equipos', 'destino_baja')) {
                $table->string('destino_baja', 200)->nullable();
            }
            if (! Schema::hasColumn('equipos', 'responsable_baja_id')) {
                $table->unsignedBigInteger('responsable_baja_id')->nullable();
                $table->foreign('responsable_baja_id')->references('id')->on('usuarios')->onDelete('set null');
                $table->index('responsable_baja_id', 'idx_equipos_responsable_baja');
            }
            if (! Schema::hasColumn('equipos', 'nota_baja')) {
                $table->text('nota_baja')->nullable()->comment('Nota legal/tecnica del acta');
            }
            if (! Schema::hasColumn('equipos', 'acta_baja_hash')) {
                $table->char('acta_baja_hash', 64)->nullable()->comment('SHA-256 del contenido del acta de baja al emitirla');
            }
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            foreach (['motivo_baja', 'fecha_baja', 'valor_recuperacion', 'destino_baja', 'nota_baja', 'acta_baja_hash'] as $col) {
                if (Schema::hasColumn('equipos', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('equipos', 'responsable_baja_id')) {
                $table->dropForeign(['responsable_baja_id']);
                $table->dropIndex('idx_equipos_responsable_baja');
                $table->dropColumn('responsable_baja_id');
            }
        });
    }
};
