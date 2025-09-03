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
        Schema::table('reservaciones', function (Blueprint $table) {
            $table->foreign(['id_asesor'], 'res_asesor_fk')->references(['id_usuario'])->on('usuarios')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['ciudad_entrega'], 'res_ciudad_ent_fk')->references(['id_ciudad'])->on('ciudades')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ciudad_retiro'], 'res_ciudad_ret_fk')->references(['id_ciudad'])->on('ciudades')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['id_rentadora'], 'res_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sucursal_entrega'], 'res_suc_ent_fk')->references(['id_sucursal'])->on('sucursales')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['sucursal_retiro'], 'res_suc_ret_fk')->references(['id_sucursal'])->on('sucursales')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['id_usuario'], 'res_usr_fk')->references(['id_usuario'])->on('usuarios')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_vehiculo'], 'res_veh_fk')->references(['id_vehiculo'])->on('vehiculos')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservaciones', function (Blueprint $table) {
            $table->dropForeign('res_asesor_fk');
            $table->dropForeign('res_ciudad_ent_fk');
            $table->dropForeign('res_ciudad_ret_fk');
            $table->dropForeign('res_rent_fk');
            $table->dropForeign('res_suc_ent_fk');
            $table->dropForeign('res_suc_ret_fk');
            $table->dropForeign('res_usr_fk');
            $table->dropForeign('res_veh_fk');
        });
    }
};
