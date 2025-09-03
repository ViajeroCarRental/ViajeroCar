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
        Schema::table('vehiculo_imagenes', function (Blueprint $table) {
            $table->foreign(['id_vehiculo'], 'veh_img_veh_fk')->references(['id_vehiculo'])->on('vehiculos')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehiculo_imagenes', function (Blueprint $table) {
            $table->dropForeign('veh_img_veh_fk');
        });
    }
};
