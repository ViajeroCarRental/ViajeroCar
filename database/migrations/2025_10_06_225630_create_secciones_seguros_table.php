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
        Schema::create('secciones_seguros', function (Blueprint $table) {
            $table->bigIncrements('id_seccion');
            $table->string('nombre', 100)->unique(); // Ej: "Robo y Colisión"
            // Este booleano es la clave: le dirá al sistema si esta sección obliga a desglosar por autos
            $table->boolean('requiere_desglose_autos')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secciones_seguros');
    }
};
