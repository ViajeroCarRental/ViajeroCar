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
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->foreign(['id_categoria'], 'veh_cat_fk')->references(['id_categoria'])->on('categorias_carros')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_ciudad'], 'veh_ciudad_fk')->references(['id_ciudad'])->on('ciudades')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_estatus'], 'veh_est_fk')->references(['id_estatus'])->on('estatus_carro')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_marca'], 'veh_marca_fk')->references(['id_marca'])->on('marcas')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_modelo'], 'veh_modelo_fk')->references(['id_modelo'])->on('modelos')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_rentadora'], 'veh_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_sucursal'], 'veh_sucursal_fk')->references(['id_sucursal'])->on('sucursales')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_version'], 'vehiculos_id_version_fk')->references(['id_version'])->on('versiones')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropForeign('veh_cat_fk');
            $table->dropForeign('veh_ciudad_fk');
            $table->dropForeign('veh_est_fk');
            $table->dropForeign('veh_marca_fk');
            $table->dropForeign('veh_modelo_fk');
            $table->dropForeign('veh_rent_fk');
            $table->dropForeign('veh_sucursal_fk');
            $table->dropForeign('vehiculos_id_version_fk');
        });
    }
};
