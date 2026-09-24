<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bajas_inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventario_id');
            $table->integer('cantidad');
            $table->text('motivo')->nullable();
            $table->timestamp('fecha')->useCurrent();
            $table->unsignedBigInteger('usuario_id')->nullable();

            $table->foreign('inventario_id')->references('id')->on('inventario_items')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bajas_inventario');
    }
};
