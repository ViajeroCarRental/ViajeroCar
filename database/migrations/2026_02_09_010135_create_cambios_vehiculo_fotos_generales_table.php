<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cambios_vehiculo_fotos_generales', function (Blueprint $table) {
            $table->bigIncrements('id_foto_general');

            $table->unsignedBigInteger('id_cambio');
            $table->unsignedBigInteger('id_contrato');
            $table->unsignedBigInteger('id_reservacion')->nullable();

            // ✅ Se crea como binary y luego lo convertimos a LONGBLOB
            $table->binary('archivo');

            $table->string('mime_type', 50)->nullable();
            $table->string('nombre_archivo', 255)->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Índices
            $table->index('id_cambio', 'cvfg_cambio_idx');
            $table->index('id_contrato', 'cvfg_contrato_idx');
            $table->index('id_reservacion', 'cvfg_reservacion_idx');

            // FKs
            $table->foreign('id_cambio')
                ->references('id_cambio')->on('cambios_vehiculo')
                ->onDelete('cascade');

            $table->foreign('id_contrato')
                ->references('id_contrato')->on('contratos')
                ->onDelete('cascade');

            $table->foreign('id_reservacion')
                ->references('id_reservacion')->on('reservaciones')
                ->nullOnDelete();
        });

        // 🔥 Convertimos el campo a LONGBLOB manualmente
        DB::statement('ALTER TABLE cambios_vehiculo_fotos_generales MODIFY archivo LONGBLOB');
    }

    public function down(): void
    {
        Schema::dropIfExists('cambios_vehiculo_fotos_generales');
    }
};
