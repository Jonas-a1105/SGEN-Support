<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_consumos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('soporte_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('cantidad');
            $table->unsignedBigInteger('usuario_id');
            $table->timestamp('fecha')->useCurrent();

            $table->foreign('soporte_id')->references('id')->on('soportes')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('inventario_items')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_consumos');
    }
};
