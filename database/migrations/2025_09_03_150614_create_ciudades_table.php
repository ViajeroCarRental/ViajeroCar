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
        Schema::create('ciudades', function (Blueprint $table) {
            $table->bigIncrements('id_ciudad');
            $table->string('nombre', 120);
            $table->string('estado', 120)->nullable();
            $table->string('pais', 120)->default('México');
            $table->timestamps();

            $table->unique(['nombre', 'estado', 'pais']);
            $table->index(['pais', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ciudades');
    }
};
