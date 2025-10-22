<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seguro_paquete', function (Blueprint $table) {
            $table->bigIncrements('id_paquete');
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio_por_dia', 10, 2)->default(0.00);
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('nombre', 'seguro_paquete_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguro_paquete');
    }
};
