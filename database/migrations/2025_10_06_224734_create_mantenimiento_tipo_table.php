<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mantenimiento_tipo', function (Blueprint $table) {
            $table->smallIncrements('id_tipo');
            $table->string('clave', 60)->unique();
            $table->string('nombre', 120);
            $table->string('descripcion', 255)->nullable();
            $table->integer('periodicidad_km')->nullable();
            $table->integer('periodicidad_dias')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_tipo');
    }
};
