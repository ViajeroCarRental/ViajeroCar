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
        Schema::table('inspeccion', function (Blueprint $table) {
            $table->foreign(['id_contrato'], 'insp_ctr_fk')->references(['id_contrato'])->on('contratos')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_rentadora'], 'insp_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspeccion', function (Blueprint $table) {
            $table->dropForeign('insp_ctr_fk');
            $table->dropForeign('insp_rent_fk');
        });
    }
};
