<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mantenimiento_tipo', function (Blueprint $table) {
            $table->smallIncrements('id_tipo');
            $table->string('clave', 60)->unique();
            $table->string('nombre', 120);
            $table->string('descripcion')->nullable();
            $table->integer('periodicidad_km')->nullable();
            $table->integer('periodicidad_dias')->nullable();
            $table->boolean('activo')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_tipo');
    }
};
