<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plantillas_notificacion', function (Blueprint $table) {
            $table->bigIncrements('id_plantilla');
            $table->string('tipo', 80);
            $table->string('canal', 20);
            $table->string('asunto', 200)->nullable();
            $table->mediumText('cuerpo')->nullable();
            $table->string('idioma', 10)->default('es');
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['tipo','canal','idioma'], 'plantilla_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantillas_notificacion');
    }
};
