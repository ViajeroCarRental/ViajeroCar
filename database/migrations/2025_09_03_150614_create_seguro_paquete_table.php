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
        Schema::create('seguro_paquete', function (Blueprint $table) {
            $table->bigIncrements('id_paquete');
            $table->unsignedBigInteger('id_rentadora')->nullable()->index('sp_rent_fk');
            $table->string('nombre', 150);
            $table->string('descripcion')->nullable();
            $table->decimal('precio_por_dia', 10)->default(0);
            $table->boolean('activo')->default(true);

            $table->unique(['nombre', 'id_rentadora'], 'seguro_paquete_nombre_rent_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguro_paquete');
    }
};
