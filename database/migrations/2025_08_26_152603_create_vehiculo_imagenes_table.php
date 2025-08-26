<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('vehiculo_imagenes', function (Blueprint $table) {
            $table->id('id_imagen');
            $table->foreignId('id_vehiculo')
                  ->constrained('vehiculos', 'id_vehiculo')
                  ->onDelete('cascade');

            // Laravel no tiene longBlob(), creamos BLOB y luego lo convertimos a LONGBLOB
            $table->binary('imagen');                // placeholder (BLOB)
            $table->string('mime_type', 50)->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        // Convertir la columna a LONGBLOB (MySQL)
        DB::statement('ALTER TABLE vehiculo_imagenes MODIFY imagen LONGBLOB');
    }

    public function down(): void {
        // (opcional) Si quieres ser estricto, podrías revertir a BLOB antes de soltar la tabla:
        // DB::statement('ALTER TABLE vehiculo_imagenes MODIFY imagen BLOB');
        Schema::dropIfExists('vehiculo_imagenes');
    }
};
