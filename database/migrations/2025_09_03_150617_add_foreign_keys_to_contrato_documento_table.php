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
        Schema::table('contrato_documento', function (Blueprint $table) {
            $table->foreign(['id_contrato'], 'ctr_doc_ctr_fk')->references(['id_contrato'])->on('contratos')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_archivo'], 'ctr_doc_file_fk')->references(['id_archivo'])->on('archivos')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contrato_documento', function (Blueprint $table) {
            $table->dropForeign('ctr_doc_ctr_fk');
            $table->dropForeign('ctr_doc_file_fk');
        });
    }
};
