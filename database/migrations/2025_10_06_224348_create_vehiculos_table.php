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
            $table->string('numero_serie', 100)->nullable();
            $table->string('categoria', 100)->nullable();
            $table->year('anio');
            $table->string('nombre_publico', 150); //Que es nombre publico
            $table->string('transmision', 50)->nullable();
            $table->string('combustible', 50)->nullable();
            $table->string('color', 40)->nullable();
            $table->integer('asientos')->default(4);
            $table->integer('puertas')->default(4);
            $table->integer('kilometraje')->default(0);
            $table->integer('gasolina_actual')->nullable();
            $table->decimal('precio_dia', 10, 2)->default(0.00);
            $table->decimal('deposito_garantia', 10, 2)->default(0.00);
            $table->string('placa', 50)->nullable();

            // ✅ FALTABAN EN TU MIGRACIÓN (según DESCRIBE)
            $table->string('numero_rin', 100)->nullable();
            $table->decimal('capacidad_tanque', 6, 2)->nullable();
            $table->string('aceite', 100)->nullable();

            $table->string('vin', 100)->nullable();
            $table->string('descripcion', 255)->nullable();

            // 🔹 Datos administrativos y legales
            $table->string('tipo_servicio', 100)->nullable();
            $table->string('propietario', 150)->nullable();
            $table->string('rfc_propietario', 20)->nullable();
            $table->string('domicilio', 255)->nullable();
            $table->string('municipio', 100)->nullable();
            $table->string('estado', 100)->nullable();
            $table->string('pais', 100)->default('México');

            // 🔹 Datos técnicos y verificación
            $table->integer('cilindros')->nullable();
            $table->string('numero_motor', 100)->nullable();
            $table->string('holograma', 50)->nullable();
            $table->string('vigencia_verificacion', 100)->nullable();
            $table->string('no_centro_verificacion', 100)->nullable();
            $table->string('tipo_verificacion', 100)->nullable();

            // 🔹 Póliza de seguro
            $table->string('no_poliza', 100)->nullable();
            $table->string('aseguradora', 150)->nullable();
            $table->date('inicio_vigencia_poliza')->nullable();
            $table->date('fin_vigencia_poliza')->nullable();
            $table->string('tipo_cobertura', 100)->nullable();
            $table->string('plan_seguro', 100)->nullable();
            $table->string('archivo_poliza', 255)->nullable();

            // 🔹 Tarjeta de circulación
            $table->string('folio_tarjeta', 100)->nullable();
            $table->string('movimiento_tarjeta', 100)->nullable();
            $table->date('fecha_expedicion_tarjeta')->nullable();
            $table->string('oficina_expedidora', 100)->nullable();
            $table->string('archivo_verificacion', 255)->nullable();

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

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
