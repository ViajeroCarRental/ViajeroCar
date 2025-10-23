<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehiculo_flotilla', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_vehiculo');
            $table->unsignedBigInteger('id_flotilla');
            $table->dateTime('asignado_en')->useCurrent();
            $table->dateTime('removido_en')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['id_vehiculo', 'id_flotilla', 'asignado_en'], 'uq_veh_flotilla_asignado');
            $table->index('id_flotilla', 'vf_flotilla_idx');

            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('cascade');

            $table->foreign('id_flotilla')
                ->references('id_flotilla')->on('flotillas')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo_flotilla');
    }
};
