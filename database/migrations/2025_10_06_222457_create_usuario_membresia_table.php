<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuario_membresia', function (Blueprint $table) {
            $table->bigIncrements('id_usuario_membresia');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_membresia');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_usuario', 'um_usr_fk_idx');
            $table->index('id_membresia', 'um_mem_fk_idx');

            $table->foreign('id_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onDelete('cascade');

            $table->foreign('id_membresia')
                ->references('id_membresia')->on('membresias')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_membresia');
    }
};
