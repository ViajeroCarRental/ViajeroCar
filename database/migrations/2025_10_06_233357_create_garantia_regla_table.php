<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('garantia_regla', function (Blueprint $table) {
            $table->bigIncrements('id_regla');

            $table->unsignedBigInteger('id_categoria')->nullable();
            $table->unsignedBigInteger('id_vehiculo')->nullable();
            $table->unsignedBigInteger('id_version')->nullable();
            $table->unsignedBigInteger('id_seguro')->nullable();
            $table->unsignedBigInteger('id_paquete')->nullable();

            $table->enum('tipo', ['porcentaje','fijo']);
            $table->decimal('valor', 10, 2);
            $table->string('moneda', 10)->nullable();

            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['id_categoria', 'id_version', 'id_vehiculo'], 'gr_ambito_idx');
            $table->index(['id_seguro', 'id_paquete'], 'gr_cobertura_idx');

            $table->foreign('id_categoria')
                ->references('id_categoria')->on('categorias_carros')
                ->onDelete('cascade');

            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('cascade');

            $table->foreign('id_version')
                ->references('id_version')->on('versiones')
                ->onDelete('cascade');

            $table->foreign('id_seguro')
                ->references('id_seguro')->on('seguros')
                ->onDelete('cascade');

            $table->foreign('id_paquete')
                ->references('id_paquete')->on('seguro_paquete')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garantia_regla');
    }
};
