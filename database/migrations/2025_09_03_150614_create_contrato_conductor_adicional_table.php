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
        Schema::create('contrato_conductor_adicional', function (Blueprint $table) {
            $table->bigIncrements('id_conductor');
            $table->unsignedBigInteger('id_contrato')->index('ctr_condu_ctr_idx');
            $table->string('nombres', 120);
            $table->string('apellidos', 120);
            $table->string('numero_licencia', 80)->nullable();
            $table->string('pais_licencia', 60)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('contacto', 40)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrato_conductor_adicional');
    }
};
