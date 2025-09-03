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
        Schema::create('rentadoras', function (Blueprint $table) {
            $table->bigIncrements('id_rentadora');
            $table->string('nombre', 150)->unique('uq_rentadora_nombre');
            $table->string('rfc', 13)->nullable();
            $table->string('correo', 150)->nullable();
            $table->string('telefono', 25)->nullable();
            $table->string('sitio_web', 200)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentadoras');
    }
};
