<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->bigIncrements('id_usuario');
            $table->string('nombres', 120);
            $table->string('apellidos', 120);
            $table->string('correo', 150)->unique();
            $table->string('numero', 20)->nullable();
            $table->string('contrasena_hash', 255);
            $table->boolean('email_verificado')->default(false);
            $table->string('pais', 60)->nullable();
            $table->boolean('miembro_preferente')->default(false)->comment('Descuento aplicado en reservas');
            $table->boolean('activo')->default(true);
            $table->string('codigo_verificacion', 6)->nullable();
            $table->timestamp('expira_en')->nullable();
            $table->timestamps();

            $table->index('activo', 'usuarios_activo_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
