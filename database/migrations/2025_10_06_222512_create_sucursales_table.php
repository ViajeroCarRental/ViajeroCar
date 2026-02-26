<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sucursales', function (Blueprint $table) {
            $table->bigIncrements('id_sucursal');

            $table->unsignedBigInteger('id_ciudad');
            $table->string('nombre', 120);
            $table->string('direccion', 255)->nullable();
            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lng', 9, 6)->nullable();
            $table->json('horario_json')->nullable();
            $table->boolean('activo')->default(true);

            // ✅ timestamps nullable (espejo del DESCRIBE)
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Unicidad por ciudad + nombre (igual que lo tenías)
            $table->unique(['id_ciudad','nombre'], 'sucursales_ciudad_nombre_unique');

            // Índices
            $table->index('id_ciudad', 'suc_ciudad_idx');
            $table->index('activo', 'suc_activo_idx');

            // FK
            $table->foreign('id_ciudad')
                ->references('id_ciudad')->on('ciudades')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
