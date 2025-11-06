<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crear tabla 'siniestros'
     */
    public function up(): void
    {
        Schema::create('siniestros', function (Blueprint $table) {
            $table->bigIncrements('id_siniestro');

            // 🔗 Relación con vehículo
            $table->unsignedBigInteger('id_vehiculo');

            // 📋 Datos generales del siniestro
            $table->string('folio', 50)->unique();
            $table->date('fecha')->default(now());

            // ⚙️ Tipo de siniestro
            $table->enum('tipo', [
                'Recuperado',
                'Robo',
                'Robo de piezas',
                'Pérdida total',
                'Temas legales'
            ]);

            // 📌 Estatus general (abierto, cerrado, en trámite, etc.)
            $table->string('estatus', 50)->default('Abierto');

            // 💰 Deducible
            $table->decimal('deducible', 10, 2)->nullable();

            // ⚙️ Rin o referencia adicional
            $table->string('rin', 100)->nullable();

            // 📎 Archivo (PDF o imagen)
            $table->string('archivo')->nullable();

            // 🔄 Control de tiempo
            $table->timestamps();

            // 🔗 Relación con 'vehiculos'
            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')
                ->on('vehiculos')
                ->onDelete('cascade');
        });
    }

    /**
     * Eliminar tabla
     */
    public function down(): void
    {
        Schema::dropIfExists('siniestros');
    }
};
