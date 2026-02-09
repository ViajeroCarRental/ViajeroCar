<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservaciones', function (Blueprint $table) {
            $table->bigIncrements('id_reservacion');

            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_asesor')->nullable(); // ✅ faltaba
            $table->unsignedBigInteger('id_vehiculo')->nullable();
            $table->unsignedBigInteger('id_categoria')->nullable();

            $table->unsignedBigInteger('ciudad_retiro');
            $table->unsignedBigInteger('ciudad_entrega');
            $table->unsignedBigInteger('sucursal_retiro')->nullable();
            $table->unsignedBigInteger('sucursal_entrega')->nullable();

            $table->date('fecha_inicio');
            $table->time('hora_retiro')->nullable();
            $table->date('fecha_fin');
            $table->time('hora_entrega')->nullable();

            $table->enum('estado', ['hold','pendiente_pago','confirmada','cancelada','expirada'])
                  ->default('hold');

            $table->boolean('aprobado_por_superadmin')->default(false);
            $table->dateTime('hold_expires_at')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('impuestos', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->string('moneda', 10)->default('MXN');

            $table->boolean('tarifa_ajustada')->default(false);
            $table->decimal('tarifa_modificada', 10, 2)->nullable();
            $table->decimal('tarifa_base', 10, 2)->nullable();

            $table->unsignedTinyInteger('horas_cortesia')->default(1);

            $table->string('no_vuelo', 40)->nullable();
            $table->string('codigo', 50)->unique();

            $table->string('nombre_cliente', 120)->nullable();
            $table->string('apellidos_cliente', 120)->nullable(); // ✅ faltaba
            $table->string('email_cliente', 120)->nullable();
            $table->string('telefono_cliente', 40)->nullable();

            $table->string('paypal_order_id', 100)->nullable();
            $table->string('status_pago', 50)->default('Pendiente');
            $table->string('metodo_pago', 30)->default('mostrador');

            // ✅ faltaba en tu migración (existe en la tabla real)
            $table->string('firma_arrendador', 255)->nullable();

            // Delivery
            $table->boolean('delivery_activo')->default(false);
            $table->unsignedBigInteger('delivery_ubicacion')->nullable();
            $table->string('delivery_direccion', 255)->nullable();
            $table->integer('delivery_km')->default(0);
            $table->decimal('delivery_precio_km', 10, 2)->default(0.00);
            $table->decimal('delivery_total', 10, 2)->default(0.00);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Índices (reflejar MUL + optimizar)
            $table->index('id_usuario', 'res_usr_idx');
            $table->index('id_asesor', 'res_asesor_idx');
            $table->index('id_vehiculo', 'res_veh_idx');
            $table->index('id_categoria', 'res_cat_idx');
            $table->index('ciudad_retiro', 'res_ciudad_ret_idx');
            $table->index('ciudad_entrega', 'res_ciudad_ent_idx');
            $table->index('sucursal_retiro', 'res_suc_ret_idx');
            $table->index('sucursal_entrega', 'res_suc_ent_idx');

            $table->index(['estado', 'fecha_inicio', 'fecha_fin'], 'reservas_estado_fecha_idx');

            // FKs
            $table->foreign('id_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onDelete('set null');

            $table->foreign('id_asesor')
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
