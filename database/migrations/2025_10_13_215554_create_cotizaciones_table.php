<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->bigIncrements('id_cotizacion');

            // 🎫 Identificador único
            $table->string('folio', 40)->unique();

            // 🚗 Datos del vehículo o categoría (no siempre se asigna un vehículo real)
            $table->unsignedBigInteger('vehiculo_id')->nullable();
            $table->string('vehiculo_marca')->nullable();
            $table->string('vehiculo_modelo')->nullable();
            $table->string('vehiculo_categoria')->nullable();

            // 📅 Fechas y horas de entrega/devolución
            $table->date('pickup_date');
            $table->string('pickup_time', 10)->nullable();
            $table->string('pickup_name')->nullable();

            $table->date('dropoff_date');
            $table->string('dropoff_time', 10)->nullable();
            $table->string('dropoff_name')->nullable();

            // 📏 Duración de la renta
            $table->unsignedInteger('days')->default(1);

            // 💰 Totales
            $table->decimal('tarifa_base', 12, 2)->default(0);
            $table->decimal('extras_sub', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // 🧩 JSON: complementos y cliente
            $table->json('addons')->nullable();   // servicios adicionales
            $table->json('cliente')->nullable();  // nombre/email/telefono, etc.

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
