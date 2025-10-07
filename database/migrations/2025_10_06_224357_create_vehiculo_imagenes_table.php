<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehiculo_imagenes', function (Blueprint $table) {
            $table->bigIncrements('id_imagen');
            $table->unsignedBigInteger('id_vehiculo');

            // 1) Tipo compatible con Laravel (BLOB)
            $table->binary('imagen')->nullable();

            $table->string('url', 300)->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->integer('orden')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_vehiculo', 'veh_img_veh_fk_idx');
            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('cascade');
        });

        // 2) Ajuste al tipo real deseado en MySQL
        DB::statement('ALTER TABLE vehiculo_imagenes MODIFY imagen LONGBLOB NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo_imagenes');
    }
};
