<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('email', 150)->unique();
            $table->string('cedula', 15)->nullable();
            $table->string('cargo', 150)->nullable();
            $table->unsignedBigInteger('departamento_id')->nullable();
            $table->enum('rol', ['tecnico', 'administrador', 'consultor']);
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('set null');
            $table->foreign('departamento_id')->references('id')->on('departamentos')->onDelete('set null');
        });

        // Add foreign key for empleados in usuarios table now that empleados exists
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['empleado_id']);
        });
        Schema::dropIfExists('empleados');
    }
};
