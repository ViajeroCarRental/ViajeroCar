<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->bigIncrements('id_usuario_rol');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_rol')->index('usuario_rol_rol_fk');
            $table->timestamps();

            $table->unique(['id_usuario', 'id_rol'], 'usuario_rol_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_rol');
    }
};
