<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vehiculo_estatus_historial', function (Blueprint $table) {
            $table->id('id_historial');
            $table->foreignId('id_vehiculo')
                  ->constrained('vehiculos', 'id_vehiculo')
                  ->onDelete('cascade');
            $table->foreignId('id_estatus')
                  ->constrained('estatus_carro', 'id_estatus')
                  ->onDelete('cascade');
            $table->string('motivo', 255)->nullable();
            $table->timestamp('cambiado_en')->useCurrent();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('vehiculo_estatus_historial');
    }
};
