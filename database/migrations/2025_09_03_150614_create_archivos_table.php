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
        Schema::create('archivos', function (Blueprint $table) {
            $table->bigIncrements('id_archivo');
            $table->unsignedBigInteger('id_rentadora')->index('archivos_rent_idx');
            $table->string('nombre_original');
            $table->string('extension', 10)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('checksum', 64)->nullable();
            $table->enum('visibilidad', ['private', 'public'])->default('private');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archivos');
    }
};
