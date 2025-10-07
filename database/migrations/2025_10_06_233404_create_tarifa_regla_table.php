<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tarifa_regla', function (Blueprint $table) {
            $table->bigIncrements('id_tarifa_regla');

            $table->unsignedBigInteger('id_categoria')->nullable();
            $table->unsignedBigInteger('id_vehiculo')->nullable();
            $table->unsignedBigInteger('id_version')->nullable();

            $table->enum('tipo', ['temporada','weekday','duracion','anticipacion','custom']);

            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();
            // dias_semana se agrega con SET vía DB::statement más abajo

            $table->integer('min_dias')->nullable();
            $table->integer('max_dias')->nullable();
            $table->integer('min_anticipacion_dias')->nullable();
            $table->integer('max_anticipacion_dias')->nullable();

            $table->decimal('precio_fijo', 10, 2)->nullable();
            $table->decimal('multiplicador', 8, 4)->nullable();
            $table->string('moneda', 10)->nullable();

            $table->smallInteger('prioridad')->default(0);
            $table->boolean('activo')->default(true);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['id_categoria','id_version','id_vehiculo'], 'tr_ambito_idx');
            $table->index(['fecha_desde','fecha_hasta'], 'tr_fechas_idx');

            $table->foreign('id_categoria')
                ->references('id_categoria')->on('categorias_carros')
                ->onDelete('cascade');

            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('cascade');

            $table->foreign('id_version')
                ->references('id_version')->on('versiones')
                ->onDelete('cascade');
        });

        // Agregar columna SET para dias_semana (MySQL)
        DB::statement("
            ALTER TABLE tarifa_regla
            ADD COLUMN dias_semana SET('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NULL AFTER fecha_hasta
        ");

        // Check: al menos uno de precio_fijo o multiplicador debe existir
        DB::statement("
            ALTER TABLE tarifa_regla
            ADD CONSTRAINT chk_tr_precio_o_mult
            CHECK ( (precio_fijo IS NOT NULL) OR (multiplicador IS NOT NULL) )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifa_regla');
    }
};
