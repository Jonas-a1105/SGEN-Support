<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Calendario de feriados por país — los SLA laborales se saltan estos días.
 * Clave compuesta (país,fecha): configurable por sede vía administración.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feriados', function (Blueprint $table) {
            $table->id();
            $table->string('pais', 2)->default('VE')->comment('ISO-3166 alpha-2');
            $table->date('fecha');
            $table->string('nombre', 100);
            $table->timestamps();

            $table->unique(['pais', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feriados');
    }
};
