<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cliente_facturacion', function (Blueprint $table) {
            $table->bigIncrements('id_cliente_facturacion');

            $table->unsignedBigInteger('id_cliente');

            // 🧾 Datos fiscales
            $table->string('rfc', 13)->nullable();
            $table->string('razon_social', 200)->nullable();
            $table->string('uso_cfdi', 10)->nullable();
            $table->string('regimen_fiscal', 100)->nullable();
            $table->string('correo_factura', 150)->nullable();

            // 🏠 Domicilio fiscal
            $table->string('pais', 60)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('municipio', 150)->nullable();
            $table->string('localidad', 150)->nullable();
            $table->string('estado', 100)->nullable();
            $table->string('colonia', 150)->nullable();
            $table->string('calle', 200)->nullable();
            $table->string('numero_exterior', 50)->nullable();
            $table->string('numero_interior', 50)->nullable();
            $table->text('referencias')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_cliente', 'cfact_cliente_idx');

            $table->foreign('id_cliente')
                  ->references('id_cliente')->on('clientes')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_facturacion');
    }
};
