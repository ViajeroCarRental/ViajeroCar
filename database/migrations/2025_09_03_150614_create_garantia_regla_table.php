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
        Schema::create('garantia_regla', function (Blueprint $table) {
            $table->bigIncrements('id_regla');
            $table->unsignedBigInteger('id_rentadora');
            $table->unsignedBigInteger('id_categoria')->nullable();
            $table->unsignedBigInteger('id_vehiculo')->nullable()->index('gr_veh_fk');
            $table->unsignedBigInteger('id_version')->nullable()->index('gr_ver_fk');
            $table->unsignedBigInteger('id_seguro')->nullable();
            $table->unsignedBigInteger('id_paquete')->nullable()->index('gr_paq_fk');
            $table->enum('tipo', ['porcentaje', 'fijo']);
            $table->decimal('valor', 10);
            $table->string('moneda', 10)->nullable();
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();

            $table->index(['id_categoria', 'id_version', 'id_vehiculo'], 'gr_ambito_idx');
            $table->index(['id_seguro', 'id_paquete'], 'gr_cobertura_idx');
            $table->index(['id_rentadora', 'activo', 'vigente_desde', 'vigente_hasta'], 'gr_rent_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garantia_regla');
    }
};
