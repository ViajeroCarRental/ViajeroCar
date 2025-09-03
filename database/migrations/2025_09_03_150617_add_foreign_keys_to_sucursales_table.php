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
        Schema::table('sucursales', function (Blueprint $table) {
            $table->foreign(['id_ciudad'], 'suc_ciu_fk')->references(['id_ciudad'])->on('ciudades')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['id_rentadora'], 'suc_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropForeign('suc_ciu_fk');
            $table->dropForeign('suc_rent_fk');
        });
    }
};
