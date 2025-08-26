<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reservaciones', function (Blueprint $table) {
            $table->id('id_reservacion');
            $table->foreignId('id_usuario')
                  ->constrained('usuarios', 'id_usuario')
                  ->onDelete('cascade');
            $table->foreignId('id_vehiculo')
                  ->constrained('vehiculos', 'id_vehiculo')
                  ->onDelete('cascade');
            $table->foreignId('ciudad_retiro')
                  ->constrained('ciudades', 'id_ciudad');
            $table->foreignId('ciudad_entrega')
                  ->constrained('ciudades', 'id_ciudad');

            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('estado', ['hold','pendiente_pago','confirmada','cancelada','expirada'])->default('hold');
            $table->dateTime('hold_expires_at')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('impuestos', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);

            $table->string('codigo', 50)->unique(); // código de reserva
            $table->timestamps();

            $table->index(['estado', 'fecha_inicio', 'fecha_fin']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('reservaciones');
    }
};
