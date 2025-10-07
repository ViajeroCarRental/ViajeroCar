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
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // UNIQUE (nombre, estado, pais)
            $table->unique(['nombre', 'estado', 'pais'], 'ciudades_nombre_estado_pais_unique');
            // INDEX (pais, estado)
            $table->index(['pais', 'estado'], 'ciudades_pais_estado_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciudades');
    }
};
