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
        Schema::table('contrato_autorizacion', function (Blueprint $table) {
            $table->foreign(['id_contrato'], 'ctr_aut_ctr_fk')->references(['id_contrato'])->on('contratos')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contrato_autorizacion', function (Blueprint $table) {
            $table->dropForeign('ctr_aut_ctr_fk');
        });
    }
};
