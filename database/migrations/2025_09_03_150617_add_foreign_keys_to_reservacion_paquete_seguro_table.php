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
        Schema::table('reservacion_paquete_seguro', function (Blueprint $table) {
            $table->foreign(['id_paquete'], 'rps_paq_fk')->references(['id_paquete'])->on('seguro_paquete')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_reservacion'], 'rps_res_fk')->references(['id_reservacion'])->on('reservaciones')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservacion_paquete_seguro', function (Blueprint $table) {
            $table->dropForeign('rps_paq_fk');
            $table->dropForeign('rps_res_fk');
        });
    }
};
