<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Locking optimista (checklist #22): cada edición viaja con la versión que el
 * cliente vio; si al guardar la fila ya cambió, se rechaza con 409 y nada se
 * pisa en silencio.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['equipos', 'soportes'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                if (! Schema::hasColumn($table->getTable(), 'version')) {
                    $table->unsignedInteger('version')->default(1);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['equipos', 'soportes'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'version')) {
                    $table->dropColumn('version');
                }
            });
        }
    }
};
