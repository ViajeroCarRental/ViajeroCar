<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cambios_vehiculo', function (Blueprint $table) {
            $table->bigIncrements('id_cambio');

            $table->unsignedBigInteger('id_contrato');
            $table->unsignedBigInteger('id_reservacion')->nullable();

            $table->unsignedBigInteger('id_vehiculo_original')->nullable();
            $table->unsignedBigInteger('id_vehiculo_nuevo')->nullable();

            $table->unsignedBigInteger('realizado_por')->nullable();

            $table->dateTime('realizado_en')->useCurrent();

            $table->string('motivo', 255)->nullable();

            $table->enum('estado', ['en_proceso','confirmado','cancelado'])->default('en_proceso');

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Índices (porque en tu estructura aparecen como MUL)
            $table->index('id_contrato', 'cv_contrato_idx');
            $table->index('id_reservacion', 'cv_reservacion_idx');
            $table->index('id_vehiculo_original', 'cv_veh_original_idx');
            $table->index('id_vehiculo_nuevo', 'cv_veh_nuevo_idx');
            $table->index('realizado_por', 'cv_realizado_por_idx');

            // FKs (suposiciones razonables)
            $table->foreign('id_contrato')
                ->references('id_contrato')->on('contratos')
                ->onDelete('cascade');

            $table->foreign('id_reservacion')
                ->references('id_reservacion')->on('reservaciones')
                ->nullOnDelete();

            $table->foreign('id_vehiculo_original')
                ->references('id_vehiculo')->on('vehiculos')
                ->nullOnDelete();

            $table->foreign('id_vehiculo_nuevo')
                ->references('id_vehiculo')->on('vehiculos')
                ->nullOnDelete();

            // 👇 OJO: aquí puede variar el nombre de la tabla/PK de usuarios en tu sistema
            // Si tu tabla es "usuarios" con PK "id_usuario", esto queda perfecto.
            $table->foreign('realizado_por')
                ->references('id_usuario')->on('usuarios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cambios_vehiculo');
    }
};
