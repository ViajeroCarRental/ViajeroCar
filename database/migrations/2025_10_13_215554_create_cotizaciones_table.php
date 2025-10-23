<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->bigIncrements('id_cotizacion');
            $table->string('folio', 40)->unique();

            $table->unsignedBigInteger('vehiculo_id');
            $table->string('vehiculo_marca')->nullable();
            $table->string('vehiculo_modelo')->nullable();
            $table->string('vehiculo_categoria')->nullable();

            $table->date('pickup_date');
            $table->string('pickup_time', 5);
            $table->string('pickup_name')->nullable();

            $table->date('dropoff_date');
            $table->string('dropoff_time', 5);
            $table->string('dropoff_name')->nullable();

            $table->unsignedInteger('days');

            $table->decimal('tarifa_base', 12, 2)->default(0);
            $table->decimal('extras_sub', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->json('addons')->nullable();   // complementos seleccionados
            $table->json('cliente')->nullable();  // nombre/email/telefono
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
