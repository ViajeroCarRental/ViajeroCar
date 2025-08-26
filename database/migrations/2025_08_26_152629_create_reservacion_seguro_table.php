<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reservacion_seguro', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('id_reservacion')
                  ->constrained('reservaciones', 'id_reservacion')
                  ->onDelete('cascade');
            $table->foreignId('id_seguro')
                  ->constrained('seguros', 'id_seguro')
                  ->onDelete('cascade');
            $table->decimal('precio_por_dia', 10, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['id_reservacion', 'id_seguro']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('reservacion_seguro');
    }
};
