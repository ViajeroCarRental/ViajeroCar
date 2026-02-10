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

            // ✅ Campo faltante
            $table->unsignedBigInteger('id_contrato')->nullable();

            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2)->default(0.00);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Evitar duplicados (como ya lo tenías)
            $table->unique(['id_reservacion', 'id_servicio'], 'reservacion_servicio_uniq');

            // Índices (por MUL)
            $table->index('id_reservacion', 'rsv_serv_res_idx');
            $table->index('id_servicio', 'rsv_serv_srv_idx');
            $table->index('id_contrato', 'rsv_serv_ctr_idx');

            // FKs
            $table->foreign('id_reservacion')
                ->references('id_reservacion')->on('reservaciones')
                ->onDelete('cascade');

            $table->foreign('id_servicio')
                ->references('id_servicio')->on('servicios')
                ->onDelete('cascade');

            // (Opcional pero recomendable por la estructura)
            $table->foreign('id_contrato')
                ->references('id_contrato')->on('contratos')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservacion_servicio');
    }
};
