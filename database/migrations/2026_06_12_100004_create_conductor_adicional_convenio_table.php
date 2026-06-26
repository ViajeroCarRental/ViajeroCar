<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conductor_adicional_convenio', function (Blueprint $table) {
            $table->bigIncrements('id_conductor_convenio');

            $table->unsignedBigInteger('id_cliente');

            // 👤 Datos del conductor
            $table->string('nombre', 200);
            $table->date('nacimiento')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('correo', 150)->nullable();
            $table->string('identificacion', 100)->nullable();
            $table->string('licencia', 100)->nullable();
            $table->date('vigencia_licencia')->nullable();

            // ✍️ Firma del conductor (canvas → base64, texto)
            $table->longText('firma')->nullable();

            // 📎 Identificación frontal (PDF o imagen)
            $table->binary('identificacion_frontal_contenido')->nullable();
            $table->string('identificacion_frontal_nombre', 255)->nullable();
            $table->string('identificacion_frontal_mime', 100)->nullable();
            $table->string('identificacion_frontal_extension', 10)->nullable();

            // 📎 Identificación trasera (PDF o imagen)
            $table->binary('identificacion_trasera_contenido')->nullable();
            $table->string('identificacion_trasera_nombre', 255)->nullable();
            $table->string('identificacion_trasera_mime', 100)->nullable();
            $table->string('identificacion_trasera_extension', 10)->nullable();

            // 📎 Licencia frontal (PDF o imagen)
            $table->binary('licencia_frontal_contenido')->nullable();
            $table->string('licencia_frontal_nombre', 255)->nullable();
            $table->string('licencia_frontal_mime', 100)->nullable();
            $table->string('licencia_frontal_extension', 10)->nullable();

            // 📎 Licencia trasera (PDF o imagen)
            $table->binary('licencia_trasera_contenido')->nullable();
            $table->string('licencia_trasera_nombre', 255)->nullable();
            $table->string('licencia_trasera_mime', 100)->nullable();
            $table->string('licencia_trasera_extension', 10)->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_cliente', 'cac_cliente_idx');

            $table->foreign('id_cliente')
                  ->references('id_cliente')->on('clientes')
                  ->onDelete('cascade');
        });

        // 🔥 Convertir los BLOB a LONGBLOB para soportar PDF/imagen grandes (hasta 4GB)
        DB::statement('ALTER TABLE conductor_adicional_convenio MODIFY identificacion_frontal_contenido LONGBLOB');
        DB::statement('ALTER TABLE conductor_adicional_convenio MODIFY identificacion_trasera_contenido LONGBLOB');
        DB::statement('ALTER TABLE conductor_adicional_convenio MODIFY licencia_frontal_contenido LONGBLOB');
        DB::statement('ALTER TABLE conductor_adicional_convenio MODIFY licencia_trasera_contenido LONGBLOB');
    }

    public function down(): void
    {
        Schema::dropIfExists('conductor_adicional_convenio');
    }
};
