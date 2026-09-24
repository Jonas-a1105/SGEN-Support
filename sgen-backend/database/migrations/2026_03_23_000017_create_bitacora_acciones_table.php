<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_acciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->nullable()->comment('El usuario que hizo la acción');
            $table->string('username', 50)->comment('El nombre del usuario en ese momento');
            $table->string('accion', 255)->comment('Descripción de lo que hizo');
            $table->string('enlace_tipo', 50)->nullable()->comment('Tipo de enlace');
            $table->unsignedBigInteger('enlace_id')->nullable()->comment('ID del objeto enlazado');
            $table->string('entidad', 50)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_acciones');
    }
};
