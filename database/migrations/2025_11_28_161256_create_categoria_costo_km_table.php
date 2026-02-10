<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categoria_costo_km', function (Blueprint $table) {
            $table->bigIncrements('id_costo');

            // FK categoría
            $table->unsignedBigInteger('id_categoria');

            // Costo único por km según categoría
            $table->decimal('costo_km', 10, 2);

            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();


            $table->foreign('id_categoria')
                ->references('id_categoria')
                ->on('categorias_carros')
                ->onDelete('cascade');

            // Garantizamos 1 costo por categoría
            $table->unique('id_categoria', 'categoria_km_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_costo_km');
    }
};
