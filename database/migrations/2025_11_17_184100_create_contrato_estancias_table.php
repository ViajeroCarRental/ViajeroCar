<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato_estancias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_contrato');
            $table->string('lugar_estancia', 255);
            $table->timestamps();

            // Relación con tu tabla de contratos
            $table->foreign('id_contrato')
                  ->references('id_contrato')
                  ->on('contratos')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_estancias');
    }
};