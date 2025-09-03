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
        Schema::create('seguros', function (Blueprint $table) {
            $table->bigIncrements('id_seguro');
            $table->unsignedBigInteger('id_rentadora')->nullable()->index('seguros_rent_fk');
            $table->string('nombre', 120);
            $table->string('cobertura')->nullable();
            $table->string('deducible', 120)->nullable();
            $table->decimal('precio_por_dia', 10)->default(0);
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();

            $table->unique(['nombre', 'id_rentadora'], 'seguros_nombre_rentadora_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguros');
    }
};
