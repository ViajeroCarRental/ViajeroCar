<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('codigos_verificacion', function (Blueprint $table) {
            $table->id('id_codigo');
            $table->foreignId('id_usuario')
                  ->constrained('usuarios', 'id_usuario')
                  ->onDelete('cascade');
            $table->string('codigo', 10);
            $table->dateTime('expira_en');
            $table->boolean('usado')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('codigos_verificacion');
    }
};
