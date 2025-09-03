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
        Schema::table('vehiculo_estatus_historial', function (Blueprint $table) {
            $table->foreign(['id_estatus'], 'veh_hist_est_fk')->references(['id_estatus'])->on('estatus_carro')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_vehiculo'], 'veh_hist_veh_fk')->references(['id_vehiculo'])->on('vehiculos')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehiculo_estatus_historial', function (Blueprint $table) {
            $table->dropForeign('veh_hist_est_fk');
            $table->dropForeign('veh_hist_veh_fk');
        });
    }
};
