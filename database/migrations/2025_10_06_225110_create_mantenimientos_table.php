<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->bigIncrements('id_mantenimiento');
            $table->unsignedBigInteger('id_vehiculo');
            $table->unsignedSmallInteger('id_tipo')->nullable(); // FK con mantenimiento_tipo

            // Datos de control
            $table->integer('kilometraje_actual')->default(0);
            $table->integer('ultimo_km_servicio')->nullable();
            $table->integer('intervalo_km')->default(10000);
            $table->date('fecha_servicio')->nullable();
            $table->decimal('costo_servicio', 10, 2)->nullable();
            $table->text('notas')->nullable();

            // Estado automático
            $table->enum('estatus', ['verde', 'amarillo', 'rojo'])->default('verde');

            $table->timestamps();

            // Relaciones
            $table->foreign('id_vehiculo')->references('id_vehiculo')->on('vehiculos')->onDelete('cascade');
            $table->foreign('id_tipo')->references('id_tipo')->on('mantenimiento_tipo')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
