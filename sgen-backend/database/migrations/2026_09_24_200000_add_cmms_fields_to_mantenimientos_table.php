<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo Mantenimiento (CMMS) — Fase 2:
 *
 * 1. mantenimiento_materiales: piezas usadas en cada orden de trabajo.
 * 2. mantenimientos.garantia_hasta: garantía de reparación (90 días).
 * 3. mantenimientos.origen_ticket_id: vincula correctivos con tickets.
 * 4. mantenimientos.serie_padre_id: genealogía de órdenes recurrentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimiento_materiales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mantenimiento_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('cantidad');
            $table->decimal('costo_unitario', 10, 2)->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('mantenimiento_id')
                ->references('id')->on('mantenimientos')->onDelete('cascade');
            $table->foreign('item_id')
                ->references('id')->on('inventario_items')->onDelete('restrict');

            $table->index(['mantenimiento_id', 'item_id']);
        });

        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->date('garantia_hasta')->nullable()->after('costo');
            $table->unsignedBigInteger('origen_ticket_id')->nullable()->after('tecnico_id');
            $table->unsignedBigInteger('serie_padre_id')->nullable()->after('origen_ticket_id');

            $table->foreign('origen_ticket_id')
                ->references('id')->on('soportes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropForeign(['origen_ticket_id']);
            $table->dropColumn(['garantia_hasta', 'origen_ticket_id', 'serie_padre_id']);
        });

        Schema::dropIfExists('mantenimiento_materiales');
    }
};
