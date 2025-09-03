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
        Schema::create('tarifa_regla', function (Blueprint $table) {
            $table->bigIncrements('id_tarifa_regla');
            $table->unsignedBigInteger('id_rentadora');
            $table->unsignedBigInteger('id_categoria')->nullable();
            $table->unsignedBigInteger('id_vehiculo')->nullable()->index('tr_veh_fk');
            $table->unsignedBigInteger('id_version')->nullable()->index('tr_ver_fk');
            $table->enum('tipo', ['temporada', 'weekday', 'duracion', 'anticipacion', 'custom']);
            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();
            $table->set('dias_semana', ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])->nullable();
            $table->integer('min_dias')->nullable();
            $table->integer('max_dias')->nullable();
            $table->integer('min_anticipacion_dias')->nullable();
            $table->integer('max_anticipacion_dias')->nullable();
            $table->decimal('precio_fijo', 10)->nullable();
            $table->decimal('multiplicador', 8, 4)->nullable();
            $table->string('moneda', 10)->nullable();
            $table->smallInteger('prioridad')->default(0);
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();

            $table->index(['id_categoria', 'id_version', 'id_vehiculo'], 'tr_ambito_idx');
            $table->index(['fecha_desde', 'fecha_hasta'], 'tr_fechas_idx');
            $table->index(['id_rentadora', 'activo', 'tipo', 'prioridad'], 'tr_rent_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifa_regla');
    }
};
