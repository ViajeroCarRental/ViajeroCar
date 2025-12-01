<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato_cambio_fecha', function (Blueprint $table) {

            $table->bigIncrements('id');

            // 🔗 Relación con la reservación
            $table->unsignedBigInteger('id_reservacion');
            $table->foreign('id_reservacion')
                ->references('id_reservacion')->on('reservaciones')
                ->onDelete('cascade');

            // 📅 Fechas actuales y nuevas
            $table->date('fecha_anterior');
            $table->time('hora_anterior')->nullable();

            $table->date('fecha_solicitada');
            $table->time('hora_solicitada')->nullable();

            // 📌 Estado de la solicitud
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])
                  ->default('pendiente');

            // 🔐 Token único para validar la autorización desde el correo
            $table->string('token', 120)->unique();

            // 📝 Opcional: motivo del cambio
            $table->text('motivo')->nullable();

            // 🧑‍💼 Quién autorizó
            $table->string('autorizado_por', 120)->nullable();
            $table->dateTime('fecha_autorizacion')->nullable();

            // ⏱️ Timestamps
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_cambio_fecha');
    }
};
