<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_inventario', 50);
            $table->string('numero_serie', 100)->unique();
            $table->string('tipo', 100);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('imagen', 255)->nullable();
            $table->string('procesador', 100)->nullable();
            $table->string('memoria_ram', 50)->nullable();
            $table->string('almacenamiento', 100)->nullable();
            $table->string('sistema_operativo', 100)->nullable();
            $table->string('direccion_ip', 50)->nullable();
            $table->string('driver', 150)->nullable();
            $table->string('toner', 100)->nullable();
            $table->unsignedBigInteger('departamento_id')->nullable();
            $table->unsignedBigInteger('empleado_id')->nullable()->comment('Usuario Asignado');
            $table->string('ubicacion_fisica', 255)->nullable();
            $table->enum('estado', ['nuevo', 'usado', 'en_uso', 'fuera_de_servicio', 'en_reparacion', 'disponible', 'en_reserva'])->default('nuevo');
            $table->date('fecha_compra')->nullable();
            $table->string('proveedor', 150)->nullable();
            $table->string('proveedor_rif', 20)->nullable();
            $table->date('garantia')->nullable();
            $table->decimal('valor_compra', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('departamento_id')->references('id')->on('departamentos')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
