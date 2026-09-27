<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Custodias formales de equipos (Módulo 12 · checklist #6/#27).
 *
 * Una sola custodia activa por equipo (índice parcial único): al asignar un
 * nuevo custodio se cierra la vigente (fecha_fin) y se abre la nueva. La
 * firma de conformidad del custodio se conserva con evidencia probatoria
 * (hash SHA-256 del trazo + IP + agente + sello de tiempo), inmutable.
 *
 * Las FK a equipos/empleados son RESTRICT: la cadena patrimonial nunca
 * pierde su historia. Las bajas se hacen cerrando la custodia, no borrando.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custodias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipo_id');
            $table->unsignedBigInteger('empleado_id')->comment('Custodio responsable del equipo');
            $table->unsignedBigInteger('asignado_por')->nullable()->comment('Usuario del sistema que ejecutó la asignación');
            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_fin')->nullable();
            $table->string('motivo', 255)->nullable()->comment('Causa de la asignación: alta, reasignación, devolución…');
            // Evidencia probatoria de la firma del custodio.
            $table->text('firma')->nullable();
            $table->char('firma_hash_sha256', 64)->nullable();
            $table->string('firma_ip', 45)->nullable();
            $table->string('firma_user_agent', 512)->nullable();
            $table->timestamp('firmado_en')->nullable();
            $table->timestamps();

            $table->foreign('equipo_id')->references('id')->on('equipos');
            $table->foreign('empleado_id')->references('id')->on('empleados');
            $table->foreign('asignado_por')->references('id')->on('usuarios')->onDelete('set null');

            $table->index(['equipo_id', 'fecha_fin']);
            $table->index('empleado_id');
        });

        // #6 Una custodia ACTIVA por equipo, garantizada por el motor.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                'CREATE UNIQUE INDEX custodias_activa_por_equipo_unique
                 ON custodias (equipo_id) WHERE fecha_fin IS NULL'
            );
        }

        // Materializar el estado actual: cada equipo con custodio asignado
        // arranca su cadena custodial con su fecha de alta original.
        DB::statement(
            "INSERT INTO custodias (equipo_id, empleado_id, asignado_por, fecha_inicio, motivo, created_at, updated_at)
             SELECT id, empleado_id, NULL, created_at, 'asignacion_inicial', created_at, updated_at
             FROM equipos WHERE empleado_id IS NOT NULL"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('custodias');
    }
};
