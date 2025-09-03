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
        Schema::table('archivo_relacion', function (Blueprint $table) {
            $table->foreign(['id_archivo'], 'ar_file_fk')->references(['id_archivo'])->on('archivos')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archivo_relacion', function (Blueprint $table) {
            $table->dropForeign('ar_file_fk');
        });
    }
};
