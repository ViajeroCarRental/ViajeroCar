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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->bigIncrements('id_usuario');
            $table->unsignedBigInteger('id_rentadora')->index('usuarios_rentadora_idx');
            $table->string('nombres', 120);
            $table->string('apellidos', 120);
            $table->string('correo', 150);
            $table->string('numero', 20)->nullable();
            $table->string('contrasena_hash');
            $table->boolean('email_verificado')->default(false);
            $table->string('pais', 60)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['correo', 'id_rentadora'], 'usuarios_correo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
