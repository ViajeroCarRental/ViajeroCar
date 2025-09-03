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
        Schema::table('seguro_paquete_item', function (Blueprint $table) {
            $table->foreign(['id_paquete'], 'spi_paquete_fk')->references(['id_paquete'])->on('seguro_paquete')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_seguro'], 'spi_seguro_fk')->references(['id_seguro'])->on('seguros')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seguro_paquete_item', function (Blueprint $table) {
            $table->dropForeign('spi_paquete_fk');
            $table->dropForeign('spi_seguro_fk');
        });
    }
};
