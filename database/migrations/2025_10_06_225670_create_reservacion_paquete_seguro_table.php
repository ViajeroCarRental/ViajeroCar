<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservacion_paquete_seguro', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_reservacion');
            $table->unsignedBigInteger('id_paquete');
            $table->decimal('precio_por_dia', 10, 2)->default(0.00);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['id_reservacion', 'id_paquete'], 'uq_res_paquete');

            $table->foreign('id_reservacion')
                ->references('id_reservacion')->on('reservaciones')
                ->onDelete('cascade');

            $table->foreign('id_paquete')
                ->references('id_paquete')->on('seguro_paquete')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservacion_paquete_seguro');
    }
};
