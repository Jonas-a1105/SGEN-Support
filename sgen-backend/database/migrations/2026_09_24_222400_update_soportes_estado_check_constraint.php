<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE soportes DROP CONSTRAINT IF EXISTS soportes_estado_check;');
        DB::statement("ALTER TABLE soportes ADD CONSTRAINT soportes_estado_check CHECK (estado IN ('pendiente', 'en_proceso', 'en_espera', 'resuelto', 'cerrado', 'cancelado'));");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE soportes DROP CONSTRAINT IF EXISTS soportes_estado_check;');
        DB::statement("ALTER TABLE soportes ADD CONSTRAINT soportes_estado_check CHECK (estado IN ('pendiente', 'en_proceso', 'en_espera', 'resuelto'));");
    }
};
