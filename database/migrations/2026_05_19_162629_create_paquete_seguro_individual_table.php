<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('paquete_seguro_individual', function (Blueprint $table) {
            $table->unsignedBigInteger('id_paquete');
            $table->unsignedBigInteger('id_individual');

            // Llaves foráneas
            $table->foreign('id_paquete')->references('id_paquete')->on('seguro_paquete')->onDelete('cascade');
            $table->foreign('id_individual')->references('id_individual')->on('seguro_individuales')->onDelete('cascade');

            // Evitar duplicados
            $table->primary(['id_paquete', 'id_individual']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paquete_seguro_individual');
    }
};