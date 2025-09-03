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
        Schema::create('archivo_relacion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_archivo')->index('ar_file_idx');
            $table->string('entidad_tipo', 50);
            $table->unsignedBigInteger('entidad_id');
            $table->string('etiqueta', 80)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['entidad_tipo', 'entidad_id'], 'ar_entidad_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archivo_relacion');
    }
};
