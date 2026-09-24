<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->enum('rol', ['admin', 'tecnico', 'consultor'])->default('consultor');
            $table->string('tema', 10)->default('light');
            $table->unsignedBigInteger('empleado_id')->nullable()->index('fk_usuario_empleado');
            $table->unsignedBigInteger('departamento_id')->nullable()->index('fk_usuario_departamento');
            $table->timestamps();

            $table->foreign('departamento_id')->references('id')->on('departamentos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
