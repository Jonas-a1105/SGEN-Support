<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ticket_archivos')) {
            Schema::table('ticket_archivos', function (Blueprint $table) {
                if (! Schema::hasColumn('ticket_archivos', 'checksum_sha256')) {
                    $table->string('checksum_sha256', 64)->nullable()->after('tamano_bytes');
                }
            });
        }

        if (Schema::hasTable('soportes')) {
            Schema::table('soportes', function (Blueprint $table) {
                if (! Schema::hasColumn('soportes', 'fecha_resolucion')) {
                    $table->timestamp('fecha_resolucion')->nullable()->after('fecha_cierre');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ticket_archivos')) {
            Schema::table('ticket_archivos', function (Blueprint $table) {
                if (Schema::hasColumn('ticket_archivos', 'checksum_sha256')) {
                    $table->dropColumn('checksum_sha256');
                }
            });
        }

        if (Schema::hasTable('soportes')) {
            Schema::table('soportes', function (Blueprint $table) {
                if (Schema::hasColumn('soportes', 'fecha_resolucion')) {
                    $table->dropColumn('fecha_resolucion');
                }
            });
        }
    }
};
