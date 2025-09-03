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
        Schema::create('reservacion_paquete_seguro', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_reservacion');
            $table->unsignedBigInteger('id_paquete')->index('rps_paq_fk');
            $table->decimal('precio_por_dia', 10)->default(0);

            $table->unique(['id_reservacion', 'id_paquete'], 'uq_res_paquete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservacion_paquete_seguro');
    }
};
