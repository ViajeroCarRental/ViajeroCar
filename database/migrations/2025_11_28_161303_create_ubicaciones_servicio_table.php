<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicaciones_servicio', function (Blueprint $table) {
            $table->bigIncrements('id_ubicacion');

            // 🆕 Ciudad de origen de la ruta (nullable, FK a ciudades).
            // El cobro del dropoff se calcula por CIUDAD de origen, no por sucursal.
            $table->unsignedBigInteger('id_ciudad_origen')->nullable();

            $table->string('estado', 100);
            $table->string('destino', 200);
            $table->integer('km');

            // 🆕 Permisos de visibilidad (default true: las rutas existentes
            // quedan visibles en usuario y admin por defecto)
            $table->boolean('ver_usuario')->default(true);
            $table->boolean('ver_admin')->default(true);

            $table->boolean('activo')->default(true);

            // ✅ nullable como tu DESCRIBE
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Índice y FK de la ciudad de origen
            $table->index('id_ciudad_origen', 'ubic_ciudad_origen_idx');

            $table->foreign('id_ciudad_origen')
                ->references('id_ciudad')->on('ciudades')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones_servicio');
    }
};
