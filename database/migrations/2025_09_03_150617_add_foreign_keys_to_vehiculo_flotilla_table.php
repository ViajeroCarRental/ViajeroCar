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
        Schema::table('vehiculo_flotilla', function (Blueprint $table) {
            $table->foreign(['id_flotilla'], 'vf_flot_fk')->references(['id_flotilla'])->on('flotillas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_vehiculo'], 'vf_veh_fk')->references(['id_vehiculo'])->on('vehiculos')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehiculo_flotilla', function (Blueprint $table) {
            $table->dropForeign('vf_flot_fk');
            $table->dropForeign('vf_veh_fk');
        });
    }
};
