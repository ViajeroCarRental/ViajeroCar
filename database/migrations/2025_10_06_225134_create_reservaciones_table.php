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

            // 🔹 Ya no se requiere vehículo obligatorio
            $table->unsignedBigInteger('id_vehiculo')->nullable();

            // 🔹 Nueva referencia: categoría reservada
            $table->unsignedBigInteger('id_categoria')->nullable();

            $table->unsignedBigInteger('ciudad_retiro');
            $table->unsignedBigInteger('ciudad_entrega');
            $table->unsignedBigInteger('sucursal_retiro')->nullable();
            $table->unsignedBigInteger('sucursal_entrega')->nullable();

            $table->date('fecha_inicio');
            $table->time('hora_retiro')->nullable();
            $table->date('fecha_fin');
            $table->time('hora_entrega')->nullable();

            $table->enum('estado', ['hold','pendiente_pago','confirmada','cancelada','expirada'])->default('hold');

            // 🧩 Nuevo campo: control de cambios de fecha por el superadmin
            $table->boolean('aprobado_por_superadmin')
                  ->default(false)
                  ->comment('Indica si el superadmin aprobó cambios de fecha de salida');

            $table->dateTime('hold_expires_at')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('impuestos', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->string('moneda', 10)->default('MXN');

            // 🟡 Nuevo campo para saber si la tarifa fue modificada manualmente
            $table->boolean('tarifa_ajustada')->default(false)->comment('Indica si la tarifa fue modificada manualmente por el asesor');
            $table->decimal('tarifa_modificada', 10, 2)->nullable()->comment('Tarifa base diaria real (modificada o no)');

            $table->string('no_vuelo', 40)->nullable();
            $table->string('codigo', 50);

            // 🔹 Campos opcionales del cliente anónimo
            $table->string('nombre_cliente', 120)->nullable();
            $table->string('email_cliente', 120)->nullable();
            $table->string('telefono_cliente', 40)->nullable();

            // 🔹 Campos agregados para manejo de pagos 💳
            $table->string('paypal_order_id', 100)->nullable()->comment('ID de la orden PayPal');
            $table->string('status_pago', 50)->default('Pendiente')->comment('Estado del pago: Pendiente, Pagado, Fallido');
            $table->string('metodo_pago', 30)->default('mostrador')->comment('Tipo de pago: mostrador o en línea');

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('codigo', 'reservaciones_codigo_unique');
            $table->index(['estado', 'fecha_inicio', 'fecha_fin'], 'reservas_estado_fecha_idx');

            // FKs
            $table->foreign('id_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onDelete('set null');

            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('set null');

            $table->foreign('id_categoria')
                ->references('id_categoria')->on('categorias_carros')
                ->onDelete('set null');

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
