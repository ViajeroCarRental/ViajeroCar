<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->bigIncrements('id_vehiculo');

            // FKs
            $table->unsignedBigInteger('id_ciudad');
            $table->unsignedBigInteger('id_sucursal')->nullable();
            $table->unsignedBigInteger('id_categoria');
            $table->unsignedBigInteger('id_estatus');
            $table->unsignedBigInteger('id_marca')->nullable();
            $table->unsignedBigInteger('id_modelo')->nullable();
            $table->unsignedBigInteger('id_version')->nullable();

            // Datos
            $table->string('marca', 100);
            $table->string('modelo', 100);
            $table->year('anio');
            $table->string('nombre_publico', 150);
            $table->string('transmision', 50)->nullable();
            $table->string('combustible', 50)->nullable();
            $table->string('color', 40)->nullable();
            $table->integer('asientos')->default(4);
            $table->integer('puertas')->default(4);
            $table->integer('kilometraje')->default(0);
            $table->decimal('precio_dia', 10, 2)->default(0.00);
            $table->decimal('deposito_garantia', 10, 2)->default(0.00);
            $table->string('placa', 50)->nullable();
            $table->string('vin', 100)->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Uniques
            $table->unique('placa', 'vehiculos_placa_unique');
            $table->unique('vin', 'vehiculos_vin_unique');

            // Índices
            $table->index(['id_ciudad', 'id_sucursal', 'id_estatus', 'precio_dia'], 'vehiculos_geo_idx');
            $table->index(['id_marca', 'id_modelo'], 'vehiculos_norm_idx');
            $table->index('id_version', 'vehiculos_version_idx');

            // FKs
            $table->foreign('id_ciudad')
                ->references('id_ciudad')->on('ciudades')
                ->onDelete('cascade');

            $table->foreign('id_sucursal')
                ->references('id_sucursal')->on('sucursales')
                ->onDelete('set null');

            $table->foreign('id_categoria')
                ->references('id_categoria')->on('categorias_carros')
                ->onDelete('cascade');

            $table->foreign('id_estatus')
                ->references('id_estatus')->on('estatus_carro')
                ->onDelete('cascade');

            $table->foreign('id_marca')
                ->references('id_marca')->on('marcas')
                ->onDelete('set null');

            $table->foreign('id_modelo')
                ->references('id_modelo')->on('modelos')
                ->onDelete('set null');

            $table->foreign('id_version')
                ->references('id_version')->on('versiones')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
