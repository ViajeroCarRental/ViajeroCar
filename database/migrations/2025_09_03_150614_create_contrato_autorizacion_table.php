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
        Schema::create('contrato_autorizacion', function (Blueprint $table) {
            $table->bigIncrements('id_autorizacion');
            $table->unsignedBigInteger('id_contrato')->index('ctr_aut_ctr_idx');
            $table->string('motivo', 200);
            $table->string('otp', 10)->nullable();
            $table->unsignedBigInteger('autorizado_por')->nullable();
            $table->dateTime('autorizado_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrato_autorizacion');
    }
};
