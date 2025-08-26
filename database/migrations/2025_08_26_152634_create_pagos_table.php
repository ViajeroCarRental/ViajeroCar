<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id_pago');
            $table->foreignId('id_reservacion')
                  ->constrained('reservaciones', 'id_reservacion')
                  ->onDelete('cascade');
            $table->string('pasarela', 50); // Stripe, PayPal, etc.
            $table->string('referencia_pasarela', 100)->unique();
            $table->enum('estatus', ['pending','authorized','paid','failed','refunded'])->default('pending');
            $table->decimal('monto', 10, 2)->default(0.00);
            $table->string('moneda', 10)->default('MXN');
            $table->json('payload_webhook')->nullable();
            $table->dateTime('fecha_autorizacion')->nullable();
            $table->timestamps();

            $table->index(['estatus']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('pagos');
    }
};
