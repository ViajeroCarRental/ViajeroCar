<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('contacto', function (Blueprint $table) {
            $table->id('id_contacto');
            $table->foreignId('id_usuario')
                  ->nullable()
                  ->constrained('usuarios', 'id_usuario')
                  ->onDelete('set null');
            $table->string('nombre', 120)->nullable();
            $table->string('email', 150);
            $table->string('asunto', 150)->nullable();
            $table->text('mensaje');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('contacto');
    }
};
