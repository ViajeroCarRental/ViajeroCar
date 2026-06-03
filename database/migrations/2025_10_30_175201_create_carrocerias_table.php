<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrocerias', function (Blueprint $table) {
            $table->bigIncrements('id_carroceria');

            // 🔗 Relación con vehículo
            $table->unsignedBigInteger('id_vehiculo');

            // 📋 Datos del reporte
            $table->string('folio', 20)->unique();
            $table->date('fecha');
            $table->string('zona_afectada', 100);
            $table->string('tipo_danio', 100);
            $table->enum('severidad', ['Leve', 'Media', 'Alta'])->default('Leve');
            $table->string('taller', 120)->nullable();
            $table->decimal('costo_estimado', 10, 2)->default(0);
            $table->enum('estatus', ['Pendiente', 'Cotizado', 'En proceso', 'Refacciones', 'Terminado'])->default('Pendiente');

            // 🔹 Archivos guardados en BD (LONGBLOB)
            $table->binary('foto_carroceria')->nullable();

            // ⏰ Tiempos de control (nullable como en tu tabla real)
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();


            // 🔗 FK con 'vehiculos'
            $table->foreign('id_vehiculo')
                  ->references('id_vehiculo')
                  ->on('vehiculos')
                  ->onDelete('cascade');
        });

        // 🔥  Convertimos los campos a LONGBLOB manualmente
        DB::statement('ALTER TABLE carrocerias MODIFY foto_carroceria LONGBLOB');
    }

    public function down(): void
    {
        Schema::dropIfExists('carrocerias');
    }
};
