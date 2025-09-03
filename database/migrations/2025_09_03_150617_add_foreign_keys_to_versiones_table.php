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
        Schema::table('versiones', function (Blueprint $table) {
            $table->foreign(['id_modelo'], 'versiones_id_modelo_fk')->references(['id_modelo'])->on('modelos')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('versiones', function (Blueprint $table) {
            $table->dropForeign('versiones_id_modelo_fk');
        });
    }
};
