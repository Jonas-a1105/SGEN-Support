<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soportes', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fecha')->useCurrent();
            $table->string('titulo', 255)->nullable();
            $table->text('descripcion');
            $table->text('observaciones')->nullable();
            $table->longText('firma')->nullable();
            $table->enum('estado', ['pendiente', 'en_proceso', 'en_espera', 'resuelto'])->default('pendiente')->index();
            $table->enum('prioridad', ['baja', 'media', 'alta', 'critica'])->default('media');
            $table->dateTime('fecha_vencimiento')->nullable();
            $table->boolean('notificacion_vencimiento_enviada')->default(false);
            $table->dateTime('notificacion_vencimiento_fecha')->nullable();
            $table->text('solucion')->nullable();
            $table->unsignedBigInteger('equipo_id');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->unsignedBigInteger('empleado_id')->nullable();
            $table->unsignedBigInteger('usuario_creacion_id')->nullable();
            $table->dateTime('fecha_asignacion')->nullable();
            $table->dateTime('fecha_cierre')->nullable();
            $table->integer('tiempo_atencion_minutos')->nullable();
            $table->enum('valoracion', ['excelente', 'bueno', 'regular', 'malo'])->nullable();
            $table->text('valoracion_comentario')->nullable();
            $table->dateTime('valoracion_fecha')->nullable();
            $table->timestamps();

            $table->foreign('equipo_id')->references('id')->on('equipos')->onDelete('cascade');
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('set null');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');
            $table->foreign('usuario_creacion_id')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soportes');
    }
};
