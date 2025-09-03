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
        Schema::create('mantenimiento_programado', function (Blueprint $table) {
            $table->bigIncrements('id_prog');
            $table->unsignedBigInteger('id_vehiculo');
            $table->unsignedSmallInteger('id_tipo')->index('mp_tipo_fk');
            $table->integer('proximo_km')->nullable();
            $table->date('proxima_fecha')->nullable();
            $table->boolean('activo')->default(true);
            $table->string('notas')->nullable();

            $table->unique(['id_vehiculo', 'id_tipo'], 'uq_prog_veh_tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_programado');
    }
};
