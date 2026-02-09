<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacto', function (Blueprint $table) {
            $table->bigIncrements('id_contacto');
            $table->string('nombre', 120);
            $table->string('telefono', 20);
            $table->string('email', 120);
            $table->string('asunto', 150)->nullable();
            $table->text('mensaje');
            $table->boolean('promociones')->default(false);
            $table->string('origen', 50)->nullable()->comment('Ej: contacto_web, campaña, etc.');
            $table->timestamp('fecha_envio')->useCurrent();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacto');
    }
};
