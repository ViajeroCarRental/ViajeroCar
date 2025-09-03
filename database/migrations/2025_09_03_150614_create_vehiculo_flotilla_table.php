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
        Schema::create('vehiculo_flotilla', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_vehiculo');
            $table->unsignedBigInteger('id_flotilla')->index('vf_flotilla_idx');
            $table->dateTime('asignado_en')->useCurrent();
            $table->dateTime('removido_en')->nullable();

            $table->unique(['id_vehiculo', 'id_flotilla', 'asignado_en'], 'uq_veh_flotilla_asignado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculo_flotilla');
    }
};
