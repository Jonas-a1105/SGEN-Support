<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Evidencia forense: eliminar un usuario NO debe borrar en cascada su
 * historial de sesiones (`sesiones_log`). La columna `username` conserva la
 * identidad incluso cuando el usuario ya no existe, y `usuario_id` pasa a
 * NULL en lugar de destruir el registro.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesiones_log', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
        });

        Schema::table('sesiones_log', function (Blueprint $table) {
            $table->unsignedBigInteger('usuario_id')->nullable()->change();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sesiones_log', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
        });

        Schema::table('sesiones_log', function (Blueprint $table) {
            $table->unsignedBigInteger('usuario_id')->nullable(false)->change();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }
};
