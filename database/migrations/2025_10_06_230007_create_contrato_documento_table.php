<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contrato_documento', function (Blueprint $table) {
            $table->bigIncrements('id_documento');
            $table->unsignedBigInteger('id_contrato');
            $table->unsignedBigInteger('id_conductor')->nullable(); // null = titular, >0 = adicional

            // Tipo general: 'licencia', 'identificacion' u 'otro'
            $table->enum('tipo', ['licencia','identificacion','otro']);

            // 🪪 Datos de identificación / licencia
            $table->string('tipo_identificacion', 50)->nullable(); // INE, Pasaporte, Cédula, etc.
            $table->string('numero_identificacion', 50)->nullable();
            $table->string('nombre', 100)->nullable();
            $table->string('apellido_paterno', 100)->nullable();
            $table->string('apellido_materno', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('pais_emision', 100)->nullable();

            // 📎 Archivos asociados (front y back)
            $table->unsignedBigInteger('id_archivo_frente')->nullable();
            $table->unsignedBigInteger('id_archivo_reverso')->nullable();

            // ✅ Estado de verificación
            $table->unsignedBigInteger('verificado_por')->nullable();
            $table->dateTime('verificado_en')->nullable();

            $table->timestamps();

            // 🔗 Índices y llaves foráneas
            $table->index('id_contrato', 'ctr_doc_ctr_idx');
            $table->foreign('id_contrato')
                ->references('id_contrato')->on('contratos')
                ->onDelete('cascade');

            $table->foreign('id_conductor')
                ->references('id_conductor')->on('contrato_conductor_adicional')
                ->onDelete('cascade');

            $table->foreign('id_archivo_frente')
                ->references('id_archivo')->on('archivos')
                ->onDelete('restrict');

            $table->foreign('id_archivo_reverso')
                ->references('id_archivo')->on('archivos')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_documento');
    }
};
