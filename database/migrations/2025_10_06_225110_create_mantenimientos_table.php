<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->bigIncrements('id_mantenimiento');

            // Relaciones
            $table->unsignedBigInteger('id_vehiculo');
            $table->unsignedSmallInteger('id_tipo')->nullable();

            // Datos base
            $table->integer('kilometraje_actual')->default(0);
            $table->integer('ultimo_km_servicio')->nullable();
            $table->integer('intervalo_km')->default(10000);
            $table->integer('proximo_servicio')->nullable(); // calculado
            $table->date('fecha_servicio')->nullable();
            $table->decimal('costo_servicio', 10, 2)->nullable();

            // Detalles técnicos
            $table->boolean('cambio_aceite')->default(false);
            $table->string('tipo_aceite', 100)->nullable();
            $table->boolean('rotacion_llantas')->default(false);
            $table->boolean('cambio_filtro')->default(false);
            $table->boolean('cambio_pastillas')->default(false);

            // Información adicional
            $table->text('observaciones')->nullable();

            // Estado automático de color
            $table->enum('estatus', ['verde', 'amarillo', 'rojo'])->default('verde');

            // Timestamps
            $table->timestamps();

            // 🔗 Llaves foráneas
            $table->foreign('id_vehiculo')->references('id_vehiculo')->on('vehiculos')->onDelete('cascade');
            $table->foreign('id_tipo')->references('id_tipo')->on('mantenimiento_tipo')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
