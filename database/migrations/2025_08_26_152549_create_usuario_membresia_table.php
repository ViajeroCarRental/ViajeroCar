<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('usuario_membresia', function (Blueprint $table) {
            $table->id('id_usuario_membresia');
            $table->foreignId('id_usuario')
                  ->constrained('usuarios', 'id_usuario')
                  ->onDelete('cascade');
            $table->foreignId('id_membresia')
                  ->constrained('membresias', 'id_membresia')
                  ->onDelete('cascade');
            $table->date('fecha_inicio')->default(now());
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('usuario_membresia');
    }
};
