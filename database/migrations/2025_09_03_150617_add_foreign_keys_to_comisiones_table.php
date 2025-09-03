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
        Schema::table('comisiones', function (Blueprint $table) {
            $table->foreign(['id_contrato'], 'com_ctr_fk')->references(['id_contrato'])->on('contratos')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_reservacion'], 'com_res_fk')->references(['id_reservacion'])->on('reservaciones')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_usuario'], 'com_usr_fk')->references(['id_usuario'])->on('usuarios')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comisiones', function (Blueprint $table) {
            $table->dropForeign('com_ctr_fk');
            $table->dropForeign('com_res_fk');
            $table->dropForeign('com_usr_fk');
        });
    }
};
