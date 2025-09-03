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
        Schema::table('mantenimiento_programado', function (Blueprint $table) {
            $table->foreign(['id_tipo'], 'mp_tipo_fk')->references(['id_tipo'])->on('mantenimiento_tipo')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['id_vehiculo'], 'mp_veh_fk')->references(['id_vehiculo'])->on('vehiculos')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantenimiento_programado', function (Blueprint $table) {
            $table->dropForeign('mp_tipo_fk');
            $table->dropForeign('mp_veh_fk');
        });
    }
};
