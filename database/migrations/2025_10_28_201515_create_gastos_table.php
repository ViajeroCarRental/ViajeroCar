<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crear tabla 'gastos'
     */
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->bigIncrements('id_gasto');

            // 🔗 Relación con vehículo
            $table->unsignedBigInteger('id_vehiculo');

            // 💡 Tipo y descripción del gasto
            $table->string('tipo', 50); // mantenimiento, seguro, siniestro, combustible, etc.
            $table->string('descripcion', 255)->nullable();

            // 💰 Monto y fecha
            $table->decimal('monto', 10, 2)->default(0);
            $table->date('fecha')->default(now());

            // 🔄 Control de tiempo
            $table->timestamps();

            // 🔗 Relación con 'vehiculos'
            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('cascade');
        });
    }

    /**
     * Revertir tabla 'gastos'
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
