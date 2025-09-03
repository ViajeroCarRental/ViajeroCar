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
        Schema::create('reservacion_servicio', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_reservacion');
            $table->unsignedBigInteger('id_servicio')->index('rs_srv_fk');
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10)->default(0);
            $table->timestamps();

            $table->unique(['id_reservacion', 'id_servicio'], 'reservacion_servicio_uniq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservacion_servicio');
    }
};
