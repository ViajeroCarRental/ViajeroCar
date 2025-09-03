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
        Schema::create('talleres', function (Blueprint $table) {
            $table->bigIncrements('id_taller');
            $table->unsignedBigInteger('id_rentadora')->index('taller_rent_idx');
            $table->string('nombre', 150);
            $table->string('telefono', 25)->nullable();
            $table->string('contacto', 120)->nullable();
            $table->string('direccion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talleres');
    }
};
