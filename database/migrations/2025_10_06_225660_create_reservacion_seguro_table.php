<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservacion_seguro', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('id_reservacion');
            $table->unsignedBigInteger('id_seguro');

            $table->decimal('precio_por_dia', 10, 2)->default(0.00);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['id_reservacion', 'id_seguro'], 'reservacion_seguro_uniq');

            // ✅ Índices explícitos (para reflejar los MUL)
            $table->index('id_reservacion', 'rs_res_idx');
            $table->index('id_seguro', 'rs_seg_idx');

            $table->foreign('id_reservacion')
                ->references('id_reservacion')->on('reservaciones')
                ->onDelete('cascade');

            $table->foreign('id_seguro')
                ->references('id_seguro')->on('seguros')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservacion_seguro');
    }
};
