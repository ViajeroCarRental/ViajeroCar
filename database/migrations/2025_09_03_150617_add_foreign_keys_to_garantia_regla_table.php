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
        Schema::table('garantia_regla', function (Blueprint $table) {
            $table->foreign(['id_categoria'], 'gr_cat_fk')->references(['id_categoria'])->on('categorias_carros')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_paquete'], 'gr_paq_fk')->references(['id_paquete'])->on('seguro_paquete')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_rentadora'], 'gr_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_seguro'], 'gr_seg_fk')->references(['id_seguro'])->on('seguros')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_vehiculo'], 'gr_veh_fk')->references(['id_vehiculo'])->on('vehiculos')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_version'], 'gr_ver_fk')->references(['id_version'])->on('versiones')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('garantia_regla', function (Blueprint $table) {
            $table->dropForeign('gr_cat_fk');
            $table->dropForeign('gr_paq_fk');
            $table->dropForeign('gr_rent_fk');
            $table->dropForeign('gr_seg_fk');
            $table->dropForeign('gr_veh_fk');
            $table->dropForeign('gr_ver_fk');
        });
    }
};
