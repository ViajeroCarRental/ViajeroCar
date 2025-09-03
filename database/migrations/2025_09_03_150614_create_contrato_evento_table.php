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
        Schema::create('contrato_evento', function (Blueprint $table) {
            $table->bigIncrements('id_evento');
            $table->unsignedBigInteger('id_contrato')->index('ctr_evt_ctr_idx');
            $table->string('evento', 120);
            $table->json('detalle')->nullable();
            $table->unsignedBigInteger('realizado_por')->nullable();
            $table->dateTime('realizado_en')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrato_evento');
    }
};
