<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombres', 120);
            $table->string('apellidos', 120);
            $table->string('correo', 150)->unique();
            $table->string('numero', 20)->nullable(); // teléfono
            $table->string('contrasena_hash'); // password encriptado
            $table->boolean('email_verificado')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('usuarios');
    }
};
