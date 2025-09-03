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
        Schema::create('tarifa_dropoff', function (Blueprint $table) {
            $table->bigIncrements('id_dropoff');
            $table->unsignedBigInteger('id_rentadora');
            $table->unsignedBigInteger('id_ciudad_origen')->nullable()->index('td_origen_ciu_idx');
            $table->unsignedBigInteger('id_sucursal_origen')->nullable()->index('td_origen_suc_idx');
            $table->unsignedBigInteger('id_ciudad_destino')->nullable()->index('td_destino_ciu_idx');
            $table->unsignedBigInteger('id_sucursal_destino')->nullable()->index('td_destino_suc_idx');
            $table->enum('tipo_cobro', ['fijo', 'por_km'])->default('fijo');
            $table->decimal('monto_base', 10)->default(0);
            $table->decimal('monto_por_km', 10)->nullable();
            $table->string('moneda', 10)->default('MXN');
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();

            $table->index(['id_rentadora', 'activo', 'vigente_desde', 'vigente_hasta'], 'td_rent_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifa_dropoff');
    }
};
