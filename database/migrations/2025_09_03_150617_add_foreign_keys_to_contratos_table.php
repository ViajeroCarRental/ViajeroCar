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
        Schema::table('contratos', function (Blueprint $table) {
            $table->foreign(['id_asesor'], 'ctr_asesor_fk')->references(['id_usuario'])->on('usuarios')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_rentadora'], 'ctr_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_reservacion'], 'ctr_res_fk')->references(['id_reservacion'])->on('reservaciones')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropForeign('ctr_asesor_fk');
            $table->dropForeign('ctr_rent_fk');
            $table->dropForeign('ctr_res_fk');
        });
    }
};
