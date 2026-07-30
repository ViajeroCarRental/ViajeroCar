<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datos_fiscales', function (Blueprint $table) {
            $table->bigIncrements('id_datos_fiscales');

            $table->string('correo_cliente', 150);

            $table->string('rfc', 13);
            $table->string('razon_social', 254);
            $table->string('regimen_fiscal', 3);
            $table->string('codigo_postal', 5);
            $table->string('correo', 150);

            $table->string('facturapi_customer_id')->nullable();
            $table->boolean('predeterminado')->default(false);
            $table->timestamps();

            $table->unique(['correo_cliente', 'rfc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datos_fiscales');
    }
};