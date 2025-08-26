<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reservacion_servicio', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('id_reservacion')
                  ->constrained('reservaciones', 'id_reservacion')
                  ->onDelete('cascade');
            $table->foreignId('id_servicio')
                  ->constrained('servicios', 'id_servicio')
                  ->onDelete('cascade');
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['id_reservacion', 'id_servicio']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('reservacion_servicio');
    }
};
