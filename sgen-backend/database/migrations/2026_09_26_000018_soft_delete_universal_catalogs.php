<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soft delete universal en catálogos (punto "60"-familia y #43): ningún
 * borrado destruye evidencia de inmediato — la fila descansa en la papelera
 * con ventana de restauración y autoría del borrado en bitácora.
 *
 * Tickets: permanecen en borrado duro por administrador (auditado en
 * bitácora); la papelera cubre los catálogos operativos.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['equipos', 'inventario_items', 'departamentos', 'categorias', 'mantenimientos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                if (! Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->timestamp('deleted_at')->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['equipos', 'inventario_items', 'departamentos', 'categorias', 'mantenimientos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->dropIndex(['deleted_at']);
                    $table->dropColumn('deleted_at');
                }
            });
        }
    }
};
