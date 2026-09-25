<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alinea bitacora_acciones con el auditor forense del módulo Audit:
 * agrega user_agent y campo de identificación de máquina (IP/hostname).
 * La FK usuario_id cambia de CASCADE a SET NULL para preservar evidencia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bitacora_acciones', function (Blueprint $table) {
            $table->string('machine_name', 100)->nullable()->after('ip_address');
            $table->string('user_agent', 255)->nullable()->after('machine_name');
        });
    }

    public function down(): void
    {
        Schema::table('bitacora_acciones', function (Blueprint $table) {
            $table->dropColumn(['machine_name', 'user_agent']);
        });
    }
};
