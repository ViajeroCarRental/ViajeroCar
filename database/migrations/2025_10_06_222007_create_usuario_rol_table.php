<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->bigIncrements('id_usuario_rol');

            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->timestamps();

            // 🔹 Evita duplicados del mismo rol por usuario
            $table->unique(['id_usuario', 'id_rol'], 'usuario_rol_unique');

            // 🔹 Relaciones
            $table->foreign('id_usuario')
                ->references('id_usuario')->on('usuarios')
                ->cascadeOnDelete();

            $table->foreign('id_rol')
                ->references('id_rol')->on('roles')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_rol');
    }
};
