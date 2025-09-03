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
        Schema::create('plantillas_notificacion', function (Blueprint $table) {
            $table->bigIncrements('id_plantilla');
            $table->unsignedBigInteger('id_rentadora');
            $table->string('tipo', 80);
            $table->string('canal', 20);
            $table->string('asunto', 200)->nullable();
            $table->mediumText('cuerpo')->nullable();
            $table->string('idioma', 10)->nullable()->default('es');
            $table->boolean('activo')->default(true);

            $table->unique(['id_rentadora', 'tipo', 'canal', 'idioma'], 'plantilla_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantillas_notificacion');
    }
};
