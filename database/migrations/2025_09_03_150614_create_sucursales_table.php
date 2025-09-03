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
        Schema::create('sucursales', function (Blueprint $table) {
            $table->bigIncrements('id_sucursal');
            $table->unsignedBigInteger('id_rentadora');
            $table->unsignedBigInteger('id_ciudad')->index('suc_ciudad_idx');
            $table->string('nombre', 120);
            $table->string('direccion')->nullable();
            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lng', 9, 6)->nullable();
            $table->json('horario_json')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_rentadora', 'nombre'], 'uq_rentadora_sucursal_nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
