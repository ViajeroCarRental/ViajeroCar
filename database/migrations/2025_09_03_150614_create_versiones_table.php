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
        Schema::create('versiones', function (Blueprint $table) {
            $table->bigIncrements('id_version');
            $table->unsignedBigInteger('id_modelo');
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();

            $table->unique(['id_modelo', 'nombre'], 'uq_modelo_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versiones');
    }
};
