<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->comment('A quién se le muestra la notificación');
            $table->string('mensaje', 255);
            $table->string('enlace', 255)->nullable()->comment('URL a la que lleva');
            $table->boolean('leido')->default(false)->comment('0 = no leído, 1 = leído');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
