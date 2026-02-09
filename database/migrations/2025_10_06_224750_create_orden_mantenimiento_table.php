<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orden_mantenimiento', function (Blueprint $table) {
            $table->bigIncrements('id_orden');

            $table->unsignedBigInteger('id_vehiculo');
            $table->unsignedBigInteger('id_taller')->nullable();

            $table->enum('estatus', ['programada','en_proceso','completada','cancelada'])
                  ->default('programada');

            $table->integer('odometro_km');

            $table->date('fecha_programada')->nullable();
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();

            $table->decimal('costo_total', 12, 2)->nullable();
            $table->string('moneda', 10)->default('MXN');

            $table->string('notas', 255)->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Índices
            $table->index(['id_vehiculo', 'estatus', 'fecha_programada'], 'om_veh_estatus_programada_idx');
            $table->index('id_taller', 'om_taller_idx'); // ✅ para reflejar el MUL de id_taller

            // FKs
            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('cascade');

            $table->foreign('id_taller')
                ->references('id_taller')->on('talleres');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_mantenimiento');
    }
};
