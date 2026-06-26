<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('convenios', function (Blueprint $table) {
            $table->bigIncrements('id_convenio');

            $table->unsignedBigInteger('id_cliente');

            // 📄 Tipo de convenio según el cliente
            $table->enum('tipo', ['fisica', 'moral', 'general']);

            // ✍️ Firmas (canvas → base64, texto). Se llenan según el tipo.
            $table->longText('firma_cliente')->nullable();
            $table->longText('firma_asesor')->nullable();
            $table->longText('firma_representante')->nullable();
            $table->longText('firma_conductor')->nullable();

            // 📝 Observaciones del convenio
            $table->text('observaciones')->nullable();

            // 📎 Convenio firmado subido (PDF o imagen)
            $table->binary('convenio_firmado_contenido')->nullable();
            $table->string('convenio_firmado_nombre', 255)->nullable();
            $table->string('convenio_firmado_mime', 100)->nullable();
            $table->string('convenio_firmado_extension', 10)->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_cliente', 'conv_cliente_idx');
            $table->index('tipo', 'conv_tipo_idx');

            $table->foreign('id_cliente')
                  ->references('id_cliente')->on('clientes')
                  ->onDelete('cascade');
        });

        // 🔥 Convertir el BLOB a LONGBLOB para soportar PDF/imagen grandes (hasta 4GB)
        DB::statement('ALTER TABLE convenios MODIFY convenio_firmado_contenido LONGBLOB');
    }

    public function down(): void
    {
        Schema::dropIfExists('convenios');
    }
};
