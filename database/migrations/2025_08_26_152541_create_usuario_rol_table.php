<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id('id_usuario_rol');
            $table->foreignId('id_usuario')
                  ->constrained('usuarios', 'id_usuario')
                  ->onDelete('cascade');
            $table->foreignId('id_rol')
                  ->constrained('roles', 'id_rol')
                  ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['id_usuario', 'id_rol']); // evita duplicados
        });
    }
    public function down(): void {
        Schema::dropIfExists('usuario_rol');
    }
};
