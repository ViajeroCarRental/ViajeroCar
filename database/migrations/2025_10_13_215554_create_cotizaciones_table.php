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

            // 🚗 Datos del vehículo o categoría
            $table->unsignedBigInteger('id_categoria')->nullable();
            $table->string('categoria_nombre', 255)->nullable();

            $table->unsignedBigInteger('vehiculo_id')->nullable();
            $table->string('vehiculo_marca', 255)->nullable();
            $table->string('vehiculo_modelo', 255)->nullable();
            $table->string('vehiculo_categoria', 255)->nullable();

            // 📅 Fechas y horas
            $table->date('pickup_date');
            $table->string('pickup_time', 10)->nullable();
            $table->string('pickup_name', 255)->nullable();

            $table->date('dropoff_date');
            $table->string('dropoff_time', 10)->nullable();
            $table->string('dropoff_name', 255)->nullable();

            // 📏 Duración
            $table->unsignedInteger('days')->default(1);

            // 💰 Totales
            $table->decimal('tarifa_base', 12, 2)->default(0.00);
            $table->decimal('tarifa_modificada', 12, 2)->nullable();

            $table->boolean('tarifa_ajustada')->default(false);

            $table->decimal('extras_sub', 12, 2)->default(0.00);
            $table->decimal('iva', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);

            // 🧩 JSON: servicios, seguros, cliente
            $table->json('addons')->nullable();
            $table->json('seguro')->nullable();
            $table->json('cliente')->nullable();

            // ✅ Timestamps nullable (como tu tabla real)
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
