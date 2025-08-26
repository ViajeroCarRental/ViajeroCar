<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chatbot_mensajes', function (Blueprint $table) {
            $table->id('id_mensaje');
            $table->foreignId('id_conversacion')
                  ->constrained('chatbot_conversaciones', 'id_conversacion')
                  ->onDelete('cascade');
            $table->enum('rol', ['usuario','bot','admin'])->default('usuario');
            $table->text('mensaje');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('chatbot_mensajes');
    }
};
