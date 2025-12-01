<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('archivos', function (Blueprint $table) {
            $table->bigIncrements('id_archivo');

            $table->string('nombre_original', 255);
            $table->string('ruta', 500)->nullable();
            $table->string('tipo', 50)->nullable();

            // El campo se crea vacío; el tipo lo cambiamos después
            $table->binary('contenido')->nullable();

            $table->string('extension', 10)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('checksum', 64)->nullable();
            $table->enum('visibilidad', ['private', 'public'])->default('private');

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        // 🔥 Aquí viene la magia: convertimos el campo a LONGBLOB manualmente
        DB::statement('ALTER TABLE archivos MODIFY contenido LONGBLOB');
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos');
    }
};
