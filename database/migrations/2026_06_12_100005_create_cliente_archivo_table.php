<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cliente_archivo', function (Blueprint $table) {
            $table->bigIncrements('id_cliente_archivo');

            $table->unsignedBigInteger('id_cliente');

            // 🏷️ Qué documento es (uno por fila)
            $table->enum('tipo_documento', [
                'identificacion_frontal',
                'identificacion_trasera',
                'licencia_frontal',
                'licencia_trasera',
                'csf',
                'acta_constitutiva',
                'responsiva_cliente',
                'convenio_firmado',
            ]);

            // 📎 Contenido del archivo (PDF o imagen) — todo en la BD
            $table->binary('contenido')->nullable();
            $table->string('nombre_original', 255)->nullable();
            $table->string('mime_type', 100)->nullable()
                  ->comment('image/jpeg, image/png, application/pdf, etc.');
            $table->string('extension', 10)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_cliente', 'carch_cliente_idx');
            $table->index('tipo_documento', 'carch_tipo_idx');

            $table->foreign('id_cliente')
                  ->references('id_cliente')->on('clientes')
                  ->onDelete('cascade');
        });

        // 🔥 Convertir el BLOB a LONGBLOB para soportar PDF/imagen grandes (hasta 4GB)
        DB::statement('ALTER TABLE cliente_archivo MODIFY contenido LONGBLOB');
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_archivo');
    }
};
