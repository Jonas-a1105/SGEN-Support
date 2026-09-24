<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipo_id');
            $table->timestamp('fecha')->useCurrent();
            $table->date('proxima_fecha')->nullable()->index('idx_proxima_fecha');
            $table->enum('frecuencia', ['unica', 'mensual', 'trimestral', 'semestral', 'anual'])->default('unica');
            $table->text('descripcion');
            $table->enum('tipo_mantenimiento', ['preventivo', 'correctivo', 'predictivo'])->default('preventivo');
            $table->enum('estado', ['pendiente', 'en_proceso', 'completado', 'pospuesto', 'cancelado'])->default('pendiente')->index('idx_estado');
            $table->decimal('costo', 10, 2)->default(0.00);
            $table->string('realizado_por', 150)->nullable();
            $table->unsignedBigInteger('tecnico_id')->nullable();
            $table->json('checklist')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('equipo_id')->references('id')->on('equipos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
