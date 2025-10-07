<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contrato_documento', function (Blueprint $table) {
            $table->bigIncrements('id_documento');
            $table->unsignedBigInteger('id_contrato');
            $table->enum('tipo', ['licencia','identificacion','otro']);
            $table->unsignedBigInteger('id_archivo');
            $table->unsignedBigInteger('verificado_por')->nullable(); // sin FK (opcional)
            $table->dateTime('verificado_en')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_contrato', 'ctr_doc_ctr_idx');
            $table->index('id_archivo', 'ctr_doc_file_idx');

            $table->foreign('id_contrato')
                ->references('id_contrato')->on('contratos')
                ->onDelete('cascade');

            $table->foreign('id_archivo')
                ->references('id_archivo')->on('archivos')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_documento');
    }
};
