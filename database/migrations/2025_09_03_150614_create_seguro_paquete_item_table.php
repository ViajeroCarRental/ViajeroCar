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
        Schema::create('seguro_paquete_item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_paquete');
            $table->unsignedBigInteger('id_seguro')->index('spi_seguro_fk');

            $table->unique(['id_paquete', 'id_seguro'], 'uq_paquete_seguro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguro_paquete_item');
    }
};
