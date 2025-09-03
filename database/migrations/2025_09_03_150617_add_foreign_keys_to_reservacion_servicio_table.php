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
        Schema::table('reservacion_servicio', function (Blueprint $table) {
            $table->foreign(['id_reservacion'], 'rs_res_fk')->references(['id_reservacion'])->on('reservaciones')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_servicio'], 'rs_srv_fk')->references(['id_servicio'])->on('servicios')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservacion_servicio', function (Blueprint $table) {
            $table->dropForeign('rs_res_fk');
            $table->dropForeign('rs_srv_fk');
        });
    }
};
