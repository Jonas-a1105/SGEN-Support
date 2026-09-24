<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('origen_departamento_id')->nullable();
            $table->unsignedBigInteger('destino_departamento_id')->nullable();
            $table->enum('tipo_movimiento', ['ENTRADA', 'SALIDA', 'AJUSTE', 'BAJA', 'CONSUMO', 'TRANSFERENCIA']);
            $table->integer('cantidad');
            $table->string('motivo', 255)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable()->comment('ID de Ticket o Mantenimiento');
            $table->timestamp('fecha')->useCurrent();

            $table->foreign('item_id')->references('id')->on('inventario_items')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('origen_departamento_id')->references('id')->on('departamentos')->onDelete('set null');
            $table->foreign('destino_departamento_id')->references('id')->on('departamentos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_movimientos');
    }
};
