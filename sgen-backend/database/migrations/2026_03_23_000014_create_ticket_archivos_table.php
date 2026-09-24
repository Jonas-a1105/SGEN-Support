<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_archivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->string('nombre_archivo', 255);
            $table->string('nombre_original', 255);
            $table->string('ruta', 500);
            $table->string('tipo_mime', 100)->nullable();
            $table->integer('tamano_bytes')->nullable();
            $table->unsignedBigInteger('subido_por')->nullable();
            $table->timestamp('fecha_subida')->useCurrent();

            $table->foreign('ticket_id')->references('id')->on('soportes')->onDelete('cascade');
            $table->foreign('subido_por')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_archivos');
    }
};
