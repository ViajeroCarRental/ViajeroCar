<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rentas', function (Blueprint $table) {
            $table->id('id_renta');
            $table->foreignId('id_reservacion')
                  ->constrained('reservaciones', 'id_reservacion')
                  ->onDelete('cascade');
            $table->dateTime('fecha_entrega_real')->nullable();
            $table->integer('odometro_salida')->nullable();
            $table->string('combustible_salida', 50)->nullable();
            $table->dateTime('fecha_devolucion_real')->nullable();
            $table->integer('odometro_entrada')->nullable();
            $table->string('combustible_entrada', 50)->nullable();
            $table->decimal('cargos_extra', 10, 2)->default(0.00);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rentas');
    }
};
