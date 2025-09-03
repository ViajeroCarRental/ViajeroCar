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
        Schema::create('contrato_documento', function (Blueprint $table) {
            $table->bigIncrements('id_documento');
            $table->unsignedBigInteger('id_contrato')->index('ctr_doc_ctr_idx');
            $table->enum('tipo', ['licencia', 'identificacion', 'otro']);
            $table->unsignedBigInteger('id_archivo')->index('ctr_doc_file_idx');
            $table->unsignedBigInteger('verificado_por')->nullable();
            $table->dateTime('verificado_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrato_documento');
    }
};
