<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ubicaciones_servicio', function (Blueprint $table) {
            $table->bigIncrements('id_ubicacion');

            $table->string('estado', 100);
            $table->string('destino', 200);
            $table->integer('km');

            // Usaremos esta bandera para activar/desactivar destinos
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones_servicio');
    }
};
