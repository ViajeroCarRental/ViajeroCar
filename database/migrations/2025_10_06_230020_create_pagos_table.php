<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->bigIncrements('id_pago');

            // 🔗 Relación manual a reservación o contrato (SIN foreign keys adicionales)
            $table->unsignedBigInteger('id_reservacion')->nullable();
            $table->unsignedBigInteger('id_contrato')->nullable();

            // 🔗 Identificador de origen del pago
            $table->enum('origen_pago', ['online','mostrador','terminal','cripto'])->nullable();

            // 🔗 Ruta o nombre del archivo comprobante (solo texto)
            $table->string('comprobante', 255)->nullable();

            // ==============================
            //     CAMPOS EXISTENTES
            // ==============================
            $table->string('pasarela', 50)->nullable();
            $table->string('referencia_pasarela', 100)->nullable();

            $table->enum('estatus', [
                'pending','authorized','paid','failed','refunded'
            ])->default('pending');

            $table->string('metodo', 50)->nullable();
            $table->string('tipo_pago', 40)->nullable();

            $table->decimal('monto', 10, 2)->default(0.00);
            $table->string('moneda', 10)->default('MXN');
            $table->decimal('tasa_cambio', 12, 6)->nullable();

            $table->json('payload_webhook')->nullable();

            $table->dateTime('captured_at')->nullable();
            $table->dateTime('autorizacion_expira_en')->nullable();

            $table->string('referencia_externa', 100)->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // ==============================
            //               ÍNDICES
            // ==============================
            $table->unique('referencia_pasarela', 'pagos_referencia_pasarela_unique');
            $table->index('id_reservacion', 'pagos_res_idx');
            $table->index('id_contrato', 'pagos_ctr_idx');
            $table->index('estatus', 'pagos_estatus_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
