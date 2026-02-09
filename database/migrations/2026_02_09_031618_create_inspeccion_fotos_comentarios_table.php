<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inspeccion_fotos_comentarios', function (Blueprint $table) {
            $table->bigIncrements('id_inspeccion_fc');

            $table->unsignedBigInteger('id_reservacion');
            $table->unsignedBigInteger('id_contrato');
            $table->unsignedBigInteger('id_inspeccion');

            $table->enum('tipo', ['salida','entrada']);

            $table->enum('foto_categoria', [
                'frente',
                'parabrisas',
                'lado_conductor',
                'lado_pasajero',
                'atras',
                'interiores'
            ])->nullable();

            $table->unsignedTinyInteger('interior_index')->nullable();

            // ✅ Regla LONGBLOB: crear como binary y convertir manualmente
            $table->binary('archivo');

            $table->string('mime_type', 50)->nullable();
            $table->string('nombre_archivo', 255)->nullable();

            $table->text('comentario_cliente')->nullable();
            $table->text('danos_interiores')->nullable();

            $table->date('firma_cliente_fecha')->nullable();
            $table->time('firma_cliente_hora')->nullable();

            $table->date('entrego_fecha')->nullable();
            $table->time('entrego_hora')->nullable();

            $table->date('recibio_fecha')->nullable();
            $table->time('recibio_hora')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Índices (por los MUL)
            $table->index('id_reservacion', 'ifc_res_idx');
            $table->index('id_contrato', 'ifc_ctr_idx');
            $table->index('id_inspeccion', 'ifc_insp_idx');

            // FKs
            $table->foreign('id_reservacion')
                ->references('id_reservacion')->on('reservaciones')
                ->onDelete('cascade');

            $table->foreign('id_contrato')
                ->references('id_contrato')->on('contratos')
                ->onDelete('cascade');

            $table->foreign('id_inspeccion')
                ->references('id_inspeccion')->on('inspeccion')
                ->onDelete('cascade');
        });

        // 🔥 Convertimos el campo a LONGBLOB manualmente
        DB::statement('ALTER TABLE inspeccion_fotos_comentarios MODIFY archivo LONGBLOB');
    }

    public function down(): void
    {
        Schema::dropIfExists('inspeccion_fotos_comentarios');
    }
};
