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
        Schema::table('tarifa_dropoff', function (Blueprint $table) {
            $table->foreign(['id_ciudad_destino'], 'td_des_ciu_fk')->references(['id_ciudad'])->on('ciudades')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_sucursal_destino'], 'td_des_suc_fk')->references(['id_sucursal'])->on('sucursales')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_ciudad_origen'], 'td_ori_ciu_fk')->references(['id_ciudad'])->on('ciudades')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_sucursal_origen'], 'td_ori_suc_fk')->references(['id_sucursal'])->on('sucursales')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['id_rentadora'], 'td_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarifa_dropoff', function (Blueprint $table) {
            $table->dropForeign('td_des_ciu_fk');
            $table->dropForeign('td_des_suc_fk');
            $table->dropForeign('td_ori_ciu_fk');
            $table->dropForeign('td_ori_suc_fk');
            $table->dropForeign('td_rent_fk');
        });
    }
};
