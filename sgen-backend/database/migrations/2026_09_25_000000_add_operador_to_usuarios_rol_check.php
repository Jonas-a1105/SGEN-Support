<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Alinea el CHECK de usuarios.rol con el catálogo RBAC real del sistema.
 *
 * El request StoreUserRequest ya aceptaba 'operador', pero la restricción
 * de base de datos solo permitía ('admin','tecnico','consultor'): crear un
 * operador desde la UI explotaba con un 500 de check-violation. Esta
 * migración extiende el dominio permitido.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE usuarios DROP CONSTRAINT IF EXISTS usuarios_rol_check');
            DB::statement("ALTER TABLE usuarios ADD CONSTRAINT usuarios_rol_check CHECK (rol IN ('admin','tecnico','consultor','operador'))");
        } else {
            DB::statement("ALTER TABLE usuarios MODIFY COLUMN rol ENUM('admin','tecnico','consultor','operador') NOT NULL DEFAULT 'consultor'");
        }
    }

    public function down(): void
    {
        // Previene romper el rollback si ya existen usuarios operadores.
        DB::table('usuarios')->where('rol', 'operador')->update(['rol' => 'consultor']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE usuarios DROP CONSTRAINT IF EXISTS usuarios_rol_check');
            DB::statement("ALTER TABLE usuarios ADD CONSTRAINT usuarios_rol_check CHECK (rol IN ('admin','tecnico','consultor'))");
        } else {
            DB::statement("ALTER TABLE usuarios MODIFY COLUMN rol ENUM('admin','tecnico','consultor') NOT NULL DEFAULT 'consultor'");
        }
    }
};
