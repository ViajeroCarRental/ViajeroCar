<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservaciones', function (Blueprint $table) {
            $table->bigIncrements('id_reservacion');

            // 🔹 Puede haber usuario registrado o no
            $table->unsignedBigInteger('id_usuario')->nullable();

            // 🔹 Eliminamos id_asesor (ya no se usa)
            // $table->unsignedBigInteger('id_asesor')->nullable();

            $table->unsignedBigInteger('id_vehiculo');

            $table->unsignedBigInteger('ciudad_retiro');
            $table->unsignedBigInteger('ciudad_entrega');
            $table->unsignedBigInteger('sucursal_retiro')->nullable();
            $table->unsignedBigInteger('sucursal_entrega')->nullable();

            $table->date('fecha_inicio');
            $table->time('hora_retiro')->nullable();
            $table->date('fecha_fin');
            $table->time('hora_entrega')->nullable();

            $table->enum('estado', ['hold','pendiente_pago','confirmada','cancelada','expirada'])->default('hold');
            $table->dateTime('hold_expires_at')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('impuestos', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->string('moneda', 10)->default('MXN');

            $table->string('no_vuelo', 40)->nullable();
            $table->string('codigo', 50);

            // 🔹 Campos opcionales del cliente anónimo
            $table->string('nombre_cliente', 120)->nullable();
            $table->string('email_cliente', 120)->nullable();
            $table->string('telefono_cliente', 40)->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('codigo', 'reservaciones_codigo_unique');
            $table->index(['estado', 'fecha_inicio', 'fecha_fin'], 'reservas_estado_fecha_idx');

            // FK Usuario (cliente registrado, opcional)
            $table->foreign('id_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onDelete('set null');

            // FK Vehículo
            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('cascade');

            // FKs Ciudades / Sucursales
            $table->foreign('ciudad_retiro')
                ->references('id_ciudad')->on('ciudades');

            $table->foreign('ciudad_entrega')
                ->references('id_ciudad')->on('ciudades');

            $table->foreign('sucursal_retiro')
                ->references('id_sucursal')->on('sucursales');

            $table->foreign('sucursal_entrega')
                ->references('id_sucursal')->on('sucursales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservaciones');
    }
};
