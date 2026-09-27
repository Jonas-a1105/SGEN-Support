<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotencia de acciones destructivas (checklist #21): cada POST crítico
 * acepta header Idempotency-Key; la primera ejecución persiste la clave y
 * la respuesta, y cualquier reintento (doble clic, reenvío de red) REPRODUCE
 * esa respuesta en vez de ejecutar la acción otra vez — un solo ticket,
 * una sola firma, un solo consumo de material.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 100);
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('ruta', 255);
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->string('response_headers', 4000)->nullable();
            $table->longText('response_body')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['clave', 'usuario_id']);
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
