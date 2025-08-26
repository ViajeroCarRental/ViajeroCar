<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id('id_factura');
            $table->foreignId('id_reservacion')
                  ->constrained('reservaciones', 'id_reservacion')
                  ->onDelete('cascade');
            $table->string('folio', 100)->unique();
            $table->string('rfc', 20)->nullable();
            $table->string('razon_social', 150)->nullable();
            $table->string('domicilio', 255)->nullable();
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('impuestos', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->enum('estatus', ['emitida','cancelada'])->default('emitida');
            $table->binary('pdf')->nullable(); // guarda el PDF en binario
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('facturas');
    }
};
