<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('archivos', function (Blueprint $table) {
            $table->bigIncrements('id_archivo');

            // 📂 Datos básicos
            $table->string('nombre_original', 255);
            $table->string('ruta', 500)->nullable(); // 👈 Compatible con el controlador
            $table->string('tipo', 50)->nullable();  // 👈 Compatible con el controlador

            // 🧩 Datos técnicos opcionales (manteniendo tu diseño)
            $table->string('extension', 10)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('checksum', 64)->nullable();
            $table->enum('visibilidad', ['private', 'public'])->default('private');

            // 🕒 Timestamps
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos');
    }
};
