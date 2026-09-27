<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Papelera formal (cierra 04 · paso extra documental): los tickets y las
 * bajas administrativas caen de la bandeja y quedan restaurables por la
 * vía penal del flujo de normativa; sus attachments y comentarios públicos no
 * desaparecen con la salida bitácora (módulo 35).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soportes', function (Blueprint $table) {
            if (! Schema::hasColumn('soportes', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('soportes', function (Blueprint $table) {
            if (Schema::hasColumn('soportes', 'deleted_at')) {
                $table->dropIndex(['deleted_at']);
                $table->dropColumn('deleted_at');
            }
        });
    }
};
