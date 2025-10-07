<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sucursales', function (Blueprint $table) {
            $table->bigIncrements('id_sucursal');
            $table->unsignedBigInteger('id_ciudad');
            $table->string('nombre', 120);
            $table->string('direccion', 255)->nullable();
            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lng', 9, 6)->nullable();
            $table->json('horario_json')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('nombre', 'sucursal_nombre_unique');
            $table->index('id_ciudad', 'suc_ciudad_idx');

            $table->foreign('id_ciudad')
                ->references('id_ciudad')->on('ciudades');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
