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

            // Imagen (INE o Licencia)
            $table->string('imagen_licencia')->nullable();

            // Firma del conductor adicional (firma hecha en canvas)
            $table->string('firma_conductor')->nullable();

            // Si el conductor ya firmó
            $table->boolean('firmado')->default(false);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();


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
