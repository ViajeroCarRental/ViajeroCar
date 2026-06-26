<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cliente_moral', function (Blueprint $table) {
            $table->bigIncrements('id_cliente_moral');

            $table->unsignedBigInteger('id_cliente');

            // 🏢 Datos de la empresa
            $table->string('razon_social', 200);
            $table->string('telefono_empresa', 20)->nullable();
            $table->string('correo_empresa', 150)->nullable();

            // 👔 Representante legal
            $table->string('representante_nombre', 200);
            $table->date('representante_nacimiento')->nullable();
            $table->string('representante_telefono', 20)->nullable();
            $table->string('representante_correo', 150)->nullable();
            $table->string('representante_identificacion', 100)->nullable();
            $table->string('representante_licencia', 100)->nullable();
            $table->date('representante_vigencia_licencia')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_cliente', 'cmoral_cliente_idx');

            $table->foreign('id_cliente')
                  ->references('id_cliente')->on('clientes')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_moral');
    }
};
