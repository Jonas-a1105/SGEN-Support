<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->comment('Nombre del departamento');
            $table->string('ubicacion', 150)->nullable()->comment('Ubicación física');
            $table->string('jefe_area_nombre', 200)->nullable()->comment('Nombre del jefe de área');
            $table->unsignedBigInteger('jefe_area_id')->nullable()->index('idx_jefe_area');
            $table->string('descripcion', 500)->nullable()->comment('Descripción detallada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
