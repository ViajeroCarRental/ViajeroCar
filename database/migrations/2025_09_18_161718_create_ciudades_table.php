<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ciudades', function (Blueprint $table) {
            $table->bigIncrements('id_ciudad');
            $table->string('nombre', 120);
            $table->string('estado', 120)->nullable();
            $table->string('pais', 120)->default('México');
            $table->timestamps();

            // 🔹 Evita duplicados de ciudades con mismo nombre y estado/pais
            $table->unique(['nombre', 'estado', 'pais'], 'ciudades_nombre_estado_pais_unique');

            // 🔹 Índices para búsquedas frecuentes (por país o estado)
            $table->index(['pais', 'estado'], 'ciudades_pais_estado_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciudades');
    }
};
