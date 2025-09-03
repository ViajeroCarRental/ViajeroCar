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
        Schema::create('vehiculo_estatus_historial', function (Blueprint $table) {
            $table->bigIncrements('id_historial');
            $table->unsignedBigInteger('id_vehiculo')->index('veh_hist_veh_fk_idx');
            $table->unsignedBigInteger('id_estatus')->index('veh_hist_est_fk_idx');
            $table->string('motivo')->nullable();
            $table->timestamp('cambiado_en')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculo_estatus_historial');
    }
};
