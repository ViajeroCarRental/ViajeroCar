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
        Schema::table('cargo_adicional', function (Blueprint $table) {
            $table->foreign(['id_concepto'], 'cargo_concepto_fk')->references(['id_concepto'])->on('cargo_concepto')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['id_contrato'], 'cargo_ctr_fk')->references(['id_contrato'])->on('contratos')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_rentadora'], 'cargo_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargo_adicional', function (Blueprint $table) {
            $table->dropForeign('cargo_concepto_fk');
            $table->dropForeign('cargo_ctr_fk');
            $table->dropForeign('cargo_rent_fk');
        });
    }
};
