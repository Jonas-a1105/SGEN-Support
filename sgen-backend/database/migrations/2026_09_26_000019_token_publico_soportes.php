<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Canal público del solicitante (Módulo 03 · PASO 0 multicanal): cada ticket
 * lleva un enlace de seguimiento aleatorio de un solo propósito — ver estado,
 * comentarios públicos y firmar/calificar en su momento, todo sin login.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soportes', function (Blueprint $table) {
            if (! Schema::hasColumn('soportes', 'token_publico')) {
                $table->char('token_publico', 40)->nullable()->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('soportes', function (Blueprint $table) {
            if (Schema::hasColumn('soportes', 'token_publico')) {
                $table->dropUnique(['token_publico']);
                $table->dropColumn('token_publico');
            }
        });
    }
};
