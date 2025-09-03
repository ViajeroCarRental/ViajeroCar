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
        Schema::create('contratos', function (Blueprint $table) {
            $table->bigIncrements('id_contrato');
            $table->unsignedBigInteger('id_rentadora')->index('ctr_rent_fk');
            $table->unsignedBigInteger('id_reservacion')->index('contrato_res_idx');
            $table->unsignedBigInteger('id_asesor')->nullable()->index('ctr_asesor_idx');
            $table->string('numero_contrato', 60);
            $table->enum('estado', ['abierto', 'cerrado', 'cancelado'])->default('abierto');
            $table->dateTime('abierto_en')->nullable();
            $table->dateTime('cerrado_en')->nullable();
            $table->string('motivo_apertura_anticipada')->nullable();
            $table->string('motivo_cierre_anticipado')->nullable();
            $table->timestamps();

            $table->unique(['numero_contrato', 'id_rentadora'], 'contrato_numero_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
