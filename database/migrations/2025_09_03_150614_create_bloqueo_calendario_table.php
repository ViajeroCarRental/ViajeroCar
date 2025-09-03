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
        Schema::create('bloqueo_calendario', function (Blueprint $table) {
            $table->bigIncrements('id_bloqueo');
            $table->unsignedBigInteger('id_rentadora')->index('bc_rent_idx');
            $table->unsignedBigInteger('id_vehiculo');
            $table->enum('tipo', ['operativo', 'mantenimiento', 'logistica', 'otro'])->default('operativo');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->string('notas')->nullable();
            $table->timestamps();

            $table->index(['id_vehiculo', 'fecha_inicio', 'fecha_fin'], 'bc_veh_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloqueo_calendario');
    }
};
