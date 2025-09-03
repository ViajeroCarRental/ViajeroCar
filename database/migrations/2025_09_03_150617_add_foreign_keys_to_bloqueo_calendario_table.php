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
        Schema::table('bloqueo_calendario', function (Blueprint $table) {
            $table->foreign(['id_rentadora'], 'bc_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_vehiculo'], 'bc_veh_fk')->references(['id_vehiculo'])->on('vehiculos')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bloqueo_calendario', function (Blueprint $table) {
            $table->dropForeign('bc_rent_fk');
            $table->dropForeign('bc_veh_fk');
        });
    }
};
