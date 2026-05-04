<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paises', function (Blueprint $table) {
            $table->id('id_pais');
            $table->string('nombre', 100)->unique();
            $table->string('nombre_en', 100)->nullable();
            $table->string('codigo_iso', 3)->nullable()->comment('Ejemplo: MX, US, CA');
            $table->boolean('prioritario')->default(false)->comment('Para mostrarlos al principio de la lista');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paises');
    }
};