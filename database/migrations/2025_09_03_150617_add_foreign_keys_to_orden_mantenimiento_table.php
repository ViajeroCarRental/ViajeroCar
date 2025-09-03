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
        Schema::table('orden_mantenimiento', function (Blueprint $table) {
            $table->foreign(['id_rentadora'], 'om_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_taller'], 'om_taller_fk')->references(['id_taller'])->on('talleres')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['id_vehiculo'], 'om_veh_fk')->references(['id_vehiculo'])->on('vehiculos')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_mantenimiento', function (Blueprint $table) {
            $table->dropForeign('om_rent_fk');
            $table->dropForeign('om_taller_fk');
            $table->dropForeign('om_veh_fk');
        });
    }
};
