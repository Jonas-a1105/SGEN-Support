<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de correos enviados del sistema (checklist #45 · digest y
 * rate-limit): base del freno anti-spam. Se registra TODA salida de email
 * con su tipo y destinatario para aplicar el cooldown por tipo/usuario y
 * construir el digest agregado en lugar de correos sueltos seguidos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correos_enviados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('usuario_email', 150)->nullable();
            $table->string('tipo', 60)->comment('Familia de notificación: ticket_asignado, ticket_resuelto, sla_vencido…');
            $table->string('subject', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['usuario_id', 'tipo', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correos_enviados');
    }
};
