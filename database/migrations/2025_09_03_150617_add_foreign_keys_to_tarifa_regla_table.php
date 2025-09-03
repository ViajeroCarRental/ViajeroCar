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
        Schema::table('tarifa_regla', function (Blueprint $table) {
            $table->foreign(['id_categoria'], 'tr_cat_fk')->references(['id_categoria'])->on('categorias_carros')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_rentadora'], 'tr_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_vehiculo'], 'tr_veh_fk')->references(['id_vehiculo'])->on('vehiculos')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_version'], 'tr_ver_fk')->references(['id_version'])->on('versiones')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarifa_regla', function (Blueprint $table) {
            $table->dropForeign('tr_cat_fk');
            $table->dropForeign('tr_rent_fk');
            $table->dropForeign('tr_veh_fk');
            $table->dropForeign('tr_ver_fk');
        });
    }
};
