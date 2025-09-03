<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orden_mantenimiento', function (Blueprint $table) {
            $table->bigIncrements('id_orden');
            $table->unsignedBigInteger('id_rentadora')->index('om_rent_fk');
            $table->unsignedBigInteger('id_vehiculo');
            $table->unsignedBigInteger('id_taller')->nullable()->index('om_taller_fk');
            $table->enum('estatus', ['programada', 'en_proceso', 'completada', 'cancelada'])->default('programada');
            $table->integer('odometro_km');
            $table->date('fecha_programada')->nullable();
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->decimal('costo_total', 12)->nullable();
            $table->string('moneda', 10)->default('MXN');
            $table->string('notas')->nullable();
            $table->timestamps();

            $table->index(['id_vehiculo', 'estatus', 'fecha_programada'], 'om_veh_estatus_programada_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_mantenimiento');
    }
};
