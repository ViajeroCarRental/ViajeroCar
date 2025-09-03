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
        Schema::create('flotillas', function (Blueprint $table) {
            $table->bigIncrements('id_flotilla');
            $table->unsignedBigInteger('id_rentadora');
            $table->string('nombre', 120);
            $table->string('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->unique(['id_rentadora', 'nombre'], 'flotillas_nombre_rentadora_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flotillas');
    }
};
