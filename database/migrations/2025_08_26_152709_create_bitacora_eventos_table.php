<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bitacora_eventos', function (Blueprint $table) {
            $table->id('id_evento');
            $table->string('entidad', 100); // reservacion, vehiculo, pago, usuario, etc.
            $table->unsignedBigInteger('id_entidad')->nullable(); // referencia al registro
            $table->string('tipo_evento', 100); // creación, actualización, eliminación
            $table->json('datos')->nullable(); // información extra
            $table->string('actor', 100)->default('sistema'); // sistema/usuario/admin
            $table->timestamps();

            $table->index(['entidad','id_entidad']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('bitacora_eventos');
    }
};
