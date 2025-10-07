<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categorias_carros', function (Blueprint $table) {
            $table->bigIncrements('id_categoria');
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->decimal('descuento_miembro', 5, 2)->default(0.00)->comment('Descuento % para miembros preferentes');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices útiles
            $table->index('activo', 'cat_activo_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_carros');
    }
};
