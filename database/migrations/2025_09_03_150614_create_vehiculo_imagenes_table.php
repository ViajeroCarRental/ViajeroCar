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
        Schema::create('vehiculo_imagenes', function (Blueprint $table) {
            $table->bigIncrements('id_imagen');
            $table->unsignedBigInteger('id_vehiculo')->index('veh_img_veh_fk_idx');
            $table->binary('imagen')->nullable();
            $table->string('url', 300)->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculo_imagenes');
    }
};
