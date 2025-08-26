<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chatbot_conversaciones', function (Blueprint $table) {
            $table->id('id_conversacion');
            $table->foreignId('id_usuario')
                  ->nullable()
                  ->constrained('usuarios', 'id_usuario')
                  ->onDelete('set null');
            $table->string('canal', 50)->default('web'); // web, whatsapp, etc.
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('chatbot_conversaciones');
    }
};
