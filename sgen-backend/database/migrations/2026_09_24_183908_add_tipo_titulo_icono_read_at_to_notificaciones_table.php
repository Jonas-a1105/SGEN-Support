<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alinea la tabla notificaciones con el código del módulo Notification
 * y el comando sla:verify: agrega tipo, titulo, icono y read_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->string('tipo', 50)->default('info')->after('usuario_id');
            $table->string('titulo', 150)->nullable()->after('tipo');
            $table->string('icono', 50)->nullable()->after('enlace');
            $table->timestamp('read_at')->nullable()->after('leido');
        });
    }

    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'titulo', 'icono', 'read_at']);
        });
    }
};
