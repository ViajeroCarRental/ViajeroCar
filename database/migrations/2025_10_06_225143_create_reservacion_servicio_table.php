<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservacion_servicio', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_reservacion');
            $table->unsignedBigInteger('id_servicio');
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2)->default(0.00);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['id_reservacion', 'id_servicio'], 'reservacion_servicio_uniq');

            $table->foreign('id_reservacion')
                ->references('id_reservacion')->on('reservaciones')
                ->onDelete('cascade');

            $table->foreign('id_servicio')
                ->references('id_servicio')->on('servicios')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservacion_servicio');
    }
};
