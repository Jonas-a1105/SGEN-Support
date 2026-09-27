<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Desactivación de cuentas sin destruir la trazabilidad: un usuario con
 * historial operativo no puede eliminarse (FK estrictas), pero sí puede
 * quedar inactivo — pierde el acceso y se conserva toda su evidencia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('usuarios', 'activo')) {
                $table->boolean('activo')->default(true)->after('tema');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'activo')) {
                $table->dropColumn('activo');
            }
        });
    }
};
