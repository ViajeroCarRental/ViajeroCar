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
        Schema::create('odometro_lecturas', function (Blueprint $table) {
            $table->bigIncrements('id_lectura');
            $table->unsignedBigInteger('id_vehiculo');
            $table->integer('km');
            $table->dateTime('medido_en')->useCurrent();
            $table->string('fuente', 40)->nullable();

            $table->index(['id_vehiculo', 'medido_en'], 'ol_veh_fecha_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odometro_lecturas');
    }
};
