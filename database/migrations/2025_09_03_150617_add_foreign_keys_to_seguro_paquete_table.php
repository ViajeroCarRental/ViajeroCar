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
        Schema::table('seguro_paquete', function (Blueprint $table) {
            $table->foreign(['id_rentadora'], 'sp_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seguro_paquete', function (Blueprint $table) {
            $table->dropForeign('sp_rent_fk');
        });
    }
};
