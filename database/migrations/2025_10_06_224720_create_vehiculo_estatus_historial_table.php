<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehiculo_estatus_historial', function (Blueprint $table) {
            $table->bigIncrements('id_historial');
            $table->unsignedBigInteger('id_vehiculo');
            $table->unsignedBigInteger('id_estatus');
            $table->string('motivo', 255)->nullable();
            $table->timestamp('cambiado_en')->useCurrent();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_vehiculo', 'veh_hist_veh_fk_idx');
            $table->index('id_estatus', 'veh_hist_est_fk_idx');

            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('cascade');

            $table->foreign('id_estatus')
                ->references('id_estatus')->on('estatus_carro')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo_estatus_historial');
    }
};
