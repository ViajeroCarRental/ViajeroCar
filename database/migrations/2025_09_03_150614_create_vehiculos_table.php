<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->bigIncrements('id_vehiculo');
            $table->unsignedBigInteger('id_rentadora');
            $table->unsignedBigInteger('id_ciudad');
            $table->unsignedBigInteger('id_sucursal')->nullable()->index('veh_sucursal_fk');
            $table->unsignedBigInteger('id_categoria')->index('veh_cat_fk');
            $table->unsignedBigInteger('id_estatus')->index('veh_est_fk');
            $table->unsignedBigInteger('id_marca')->nullable();
            $table->unsignedBigInteger('id_modelo')->nullable()->index('veh_modelo_fk');
            $table->unsignedBigInteger('id_version')->nullable()->index('vehiculos_version_idx');
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
            $table->decimal('precio_dia', 10)->default(0);
            $table->decimal('deposito_garantia', 10)->default(0);
            $table->string('placa', 50)->nullable()->unique();
            $table->string('vin', 100)->nullable()->unique();
            $table->string('descripcion')->nullable();
            $table->timestamps();

            $table->index(['id_rentadora', 'id_estatus', 'id_ciudad', 'id_categoria'], 'veh_search_idx');
            $table->index(['id_ciudad', 'id_sucursal', 'id_estatus', 'precio_dia'], 'vehiculos_geo_idx');
            $table->index(['id_marca', 'id_modelo'], 'vehiculos_norm_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
