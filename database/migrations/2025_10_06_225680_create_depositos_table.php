<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('depositos', function (Blueprint $table) {
            $table->bigIncrements('id_deposito');
            $table->unsignedBigInteger('id_categoria');
            $table->unsignedBigInteger('id_paquete');
            $table->decimal('monto', 10, 2)->default(0.00);
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Relaciones foráneas con tus tablas reales
            $table->foreign('id_categoria')->references('id_categoria')->on('categorias_carros')->onDelete('cascade');
            $table->foreign('id_paquete')->references('id_paquete')->on('seguro_paquete')->onDelete('cascade');

            // Candado para que NO se repita un seguro en la misma categoría de auto
            $table->unique(['id_categoria', 'id_paquete'], 'idx_deposito_categoria_paquete_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depositos');
    }
};