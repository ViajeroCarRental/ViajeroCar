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
        Schema::create('servicios', function (Blueprint $table) {
            $table->bigIncrements('id_servicio');
            $table->unsignedBigInteger('id_rentadora')->nullable()->index('servicios_rent_fk');
            $table->string('nombre', 120);
            $table->string('descripcion')->nullable();
            $table->enum('tipo_cobro', ['por_dia', 'por_evento'])->default('por_dia');
            $table->decimal('precio', 10)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['nombre', 'id_rentadora'], 'servicios_nombre_rentadora_unique');
            $table->index(['tipo_cobro', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
