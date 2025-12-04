<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservacion_seguro_individual', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('id_reservacion');
            $table->unsignedBigInteger('id_individual');

            $table->decimal('precio_por_dia', 10, 2)->default(0.00);
            $table->integer('cantidad')->default(1); // por si hay casos futuros

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // ✔ Evitar duplicados
            $table->unique(['id_reservacion', 'id_individual'], 'uniq_reserva_individual');

            // ✔ Llave foránea → reservaciones
            $table->foreign('id_reservacion')
                  ->references('id_reservacion')->on('reservaciones')
                  ->onDelete('cascade');

            // ✔ Llave foránea → seguro_individuales
            $table->foreign('id_individual')
                  ->references('id_individual')->on('seguro_individuales')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservacion_seguro_individual');
    }
};
