<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mantenimiento_programado', function (Blueprint $table) {
            $table->bigIncrements('id_prog');
            $table->unsignedBigInteger('id_vehiculo');
            $table->unsignedSmallInteger('id_tipo');
            $table->integer('proximo_km')->nullable();
            $table->date('proxima_fecha')->nullable();
            $table->boolean('activo')->default(true);
            $table->string('notas', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['id_vehiculo', 'id_tipo'], 'uq_prog_veh_tipo');

            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('cascade');

            $table->foreign('id_tipo')
                ->references('id_tipo')->on('mantenimiento_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_programado');
    }
};
