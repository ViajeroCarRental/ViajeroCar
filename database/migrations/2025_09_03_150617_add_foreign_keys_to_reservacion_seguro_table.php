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
        Schema::table('reservacion_seguro', function (Blueprint $table) {
            $table->foreign(['id_reservacion'], 'rseg_res_fk')->references(['id_reservacion'])->on('reservaciones')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_seguro'], 'rseg_seg_fk')->references(['id_seguro'])->on('seguros')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservacion_seguro', function (Blueprint $table) {
            $table->dropForeign('rseg_res_fk');
            $table->dropForeign('rseg_seg_fk');
        });
    }
};
