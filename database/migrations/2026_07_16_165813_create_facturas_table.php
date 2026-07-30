<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();

            $table->string('facturapi_id')->nullable();      // el id que devuelve Facturapi

            // Vínculo con el contrato
            $table->unsignedBigInteger('id_contrato')->nullable();

            // Estado del proceso
            $table->enum('estatus', ['pendiente', 'timbrada', 'cancelada', 'expirada'])
                ->default('pendiente');

            // Ventana de autofacturación
            $table->timestamp('facturable_hasta')->nullable();
            $table->timestamp('fecha_timbrado')->nullable();

            // Quién facturó: admin o el propio cliente
            $table->enum('origen', ['admin', 'autofactura'])->default('admin');

            // Para relacionar facturas (anticipos, notas de crédito) a futuro
            $table->unsignedBigInteger('id_factura_relacionada')->nullable();

            $table->string('uuid')->nullable();               // folio fiscal
            $table->string('folio_reservacion');              // tu dato de negocio
            $table->string('rfc_receptor')->nullable();
            $table->string('nombre_receptor')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->string('status')->default('valid');
            $table->timestamps();

            $table->string('ruta_xml')->nullable();
            $table->string('ruta_pdf')->nullable();
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};