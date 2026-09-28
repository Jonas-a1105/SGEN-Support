<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade email (canal de recuperación/notificaciones) y la bandera de
 * "cambio obligatorio de contraseña" para cuentas con clave temporal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('usuarios', 'email')) {
                $table->string('email', 150)->nullable()->unique()->after('username');
            }
            if (! Schema::hasColumn('usuarios', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'email')) {
                $table->dropUnique(['email']);
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('usuarios', 'must_change_password')) {
                $table->dropColumn('must_change_password');
            }
        });
    }
};
