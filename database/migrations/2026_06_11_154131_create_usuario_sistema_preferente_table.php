<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuario_sistema_preferente', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_sistema');
            $table->boolean('es_preferente')->default(false);
            $table->decimal('descuento_pct', 5, 2)->default(0.00);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['id_usuario', 'id_sistema'], 'usp_usuario_sistema_unique');
            $table->index('id_usuario', 'usp_usuario_idx');
            $table->index('id_sistema', 'usp_sistema_idx');

            // Solo se mantiene la FK a sistemas
            $table->foreign('id_sistema')->references('id_sistema')
                ->on('sistemas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_sistema_preferente');
    }
};