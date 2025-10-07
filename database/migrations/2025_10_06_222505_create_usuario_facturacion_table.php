<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuario_facturacion', function (Blueprint $table) {
            $table->bigIncrements('id_usuario_facturacion');
            $table->unsignedBigInteger('id_usuario');
            $table->string('rfc', 13);
            $table->string('razon_social', 200);
            $table->string('domicilio', 255)->nullable();
            $table->string('uso_cfdi', 5)->nullable();
            $table->string('pais', 60)->default('México');
            $table->boolean('predeterminado')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['id_usuario', 'rfc'], 'uf_usr_rfc_unique');

            $table->foreign('id_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_facturacion');
    }
};
