<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->bigIncrements('id_pago');
            $table->unsignedBigInteger('id_reservacion');
            $table->string('pasarela', 50);
            $table->string('referencia_pasarela', 100);
            $table->enum('estatus', ['pending','authorized','paid','failed','refunded'])->default('pending');
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

            $table->unique('referencia_pasarela', 'pagos_referencia_pasarela_unique');
            $table->index('id_reservacion', 'pagos_res_idx');
            $table->index('estatus', 'pagos_estatus_idx');

            $table->foreign('id_reservacion')
                ->references('id_reservacion')->on('reservaciones')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
