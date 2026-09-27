<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Firma con valor probatorio sobre tickets resueltos: además del trazo
 * (base64 en `firma`) se conserva la huella SHA-256 del contenido firmado,
 * la IP y el agente de usuario del firmante y la fecha/hora exacta,
 * de modo que la conformidad del solicitante sea verificable a posteriori.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soportes', function (Blueprint $table) {
            if (! Schema::hasColumn('soportes', 'firma_hash_sha256')) {
                $table->char('firma_hash_sha256', 64)->nullable()->after('firma');
            }
            if (! Schema::hasColumn('soportes', 'firma_ip')) {
                $table->string('firma_ip', 45)->nullable()->after('firma_hash_sha256');
            }
            if (! Schema::hasColumn('soportes', 'firma_user_agent')) {
                $table->string('firma_user_agent', 512)->nullable()->after('firma_ip');
            }
            if (! Schema::hasColumn('soportes', 'firmado_en')) {
                $table->timestamp('firmado_en')->nullable()->after('firma_user_agent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('soportes', function (Blueprint $table) {
            foreach (['firma_hash_sha256', 'firma_ip', 'firma_user_agent', 'firmado_en'] as $column) {
                if (Schema::hasColumn('soportes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
