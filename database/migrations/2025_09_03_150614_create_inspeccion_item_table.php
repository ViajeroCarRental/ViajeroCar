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
        Schema::create('inspeccion_item', function (Blueprint $table) {
            $table->bigIncrements('id_item');
            $table->unsignedBigInteger('id_inspeccion')->index('insp_item_ins_fk_idx');
            $table->string('zona', 120);
            $table->string('estado', 120)->nullable();
            $table->string('danio')->nullable();
            $table->string('foto_url', 300)->nullable();
            $table->decimal('costo_estimado', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspeccion_item');
    }
};
