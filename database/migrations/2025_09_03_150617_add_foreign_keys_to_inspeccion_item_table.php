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
        Schema::table('inspeccion_item', function (Blueprint $table) {
            $table->foreign(['id_inspeccion'], 'insp_item_ins_fk')->references(['id_inspeccion'])->on('inspeccion')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspeccion_item', function (Blueprint $table) {
            $table->dropForeign('insp_item_ins_fk');
        });
    }
};
