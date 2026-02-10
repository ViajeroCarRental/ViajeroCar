<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categorias_carros', function (Blueprint $table) {
            $table->bigIncrements('id_categoria');
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 255)->nullable();

            // 💰 Precio base por día de la categoría
            $table->decimal('precio_dia', 10, 2)->default(0.00);

            // 🧾 Descuento para miembros
            $table->decimal('descuento_miembro', 5, 2)
                  ->default(0.00)
                  ->comment('Descuento % para miembros preferentes');

            // ⚙️ Estado activo/inactivo
            $table->boolean('activo')->default(true);

            // ⏰ Control de tiempo
            // ⏰ Control de tiempo (nullable como en tu tabla real)
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();


            // 📈 Índice para optimizar consultas por estado
            $table->index('activo', 'cat_activo_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_carros');
    }
};
