<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configuración global del sistema persistida en base de datos.
 *
 * Hasta ahora solo existían preferencias por usuario en caché; estas claves
 * son parámetros operativos del negocio (SLA, autocierre, seguridad) que la
 * administración puede ajustar sin desplegar código. Lectura cacheada vía
 * App\Support\Config\ConfiguracionGlobal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_global', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 120)->unique();
            $table->text('valor')->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamps();

            $table->foreign('actualizado_por')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_global');
    }
};
