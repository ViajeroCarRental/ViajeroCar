<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conductores_adicionales', function (Blueprint $table) {
            $table->bigIncrements('id_conductor');

            // Relación con reservación
            $table->unsignedBigInteger('id_reservacion');

            $table->string('nombre', 150);
            $table->integer('edad')->nullable();
            $table->string('licencia', 100);
            $table->string('vence', 50)->nullable();

            // 🆕 Imagen (licencia o INE)
            $table->string('imagen_licencia')->nullable();

            $table->boolean('firmado')->default(false);

            $table->timestamps();

            $table->foreign('id_reservacion')
                ->references('id_reservacion')->on('reservaciones')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conductores_adicionales');
    }
};
