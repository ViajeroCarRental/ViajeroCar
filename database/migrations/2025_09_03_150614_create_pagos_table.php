<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->bigIncrements('id_pago');
            $table->unsignedBigInteger('id_rentadora');
            $table->unsignedBigInteger('id_reservacion')->index('pagos_res_idx');
            $table->string('pasarela', 50);
            $table->string('referencia_pasarela', 100)->unique();
            $table->enum('estatus', ['pending', 'authorized', 'paid', 'failed', 'refunded'])->default('pending')->index('pagos_estatus_idx');
            $table->string('metodo', 50)->nullable();
            $table->string('tipo_pago', 40)->nullable();
            $table->decimal('monto', 10)->default(0);
            $table->string('moneda', 10)->default('MXN');
            $table->decimal('tasa_cambio', 12, 6)->nullable();
            $table->json('payload_webhook')->nullable();
            $table->dateTime('captured_at')->nullable();
            $table->dateTime('autorizacion_expira_en')->nullable();
            $table->string('referencia_externa', 100)->nullable();
            $table->timestamps();

            $table->index(['id_rentadora', 'estatus', 'created_at'], 'pag_busqueda_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
