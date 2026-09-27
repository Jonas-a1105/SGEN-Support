<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro forense de intentos de autenticación (exitosos y fallidos):
 * quién intentó entrar, desde qué IP y con qué agente. Base del bloqueo
 * temporal con alerta y de la detección de accesos anómalos.
 *
 * Regla #44 del checklist maestro: jamás se registra la contraseña.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intentos_login', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->index();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->boolean('exitoso')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intentos_login');
    }
};
