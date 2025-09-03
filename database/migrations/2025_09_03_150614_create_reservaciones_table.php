<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservaciones', function (Blueprint $table) {
            $table->bigIncrements('id_reservacion');
            $table->unsignedBigInteger('id_rentadora')->index('reservas_rent_idx');
            $table->unsignedBigInteger('id_usuario')->index('res_usr_fk');
            $table->unsignedBigInteger('id_asesor')->nullable()->index('res_asesor_idx');
            $table->unsignedBigInteger('id_vehiculo')->index('res_veh_fk');
            $table->unsignedBigInteger('ciudad_retiro')->index('res_ciudad_ret_fk');
            $table->unsignedBigInteger('ciudad_entrega')->index('res_ciudad_ent_fk');
            $table->unsignedBigInteger('sucursal_retiro')->nullable()->index('res_suc_ret_fk');
            $table->unsignedBigInteger('sucursal_entrega')->nullable()->index('res_suc_ent_fk');
            $table->date('fecha_inicio');
            $table->time('hora_retiro')->nullable();
            $table->date('fecha_fin');
            $table->time('hora_entrega')->nullable();
            $table->enum('estado', ['hold', 'pendiente_pago', 'confirmada', 'cancelada', 'expirada'])->default('hold');
            $table->dateTime('hold_expires_at')->nullable();
            $table->decimal('subtotal', 10)->default(0);
            $table->decimal('impuestos', 10)->default(0);
            $table->decimal('total', 10)->default(0);
            $table->string('moneda', 10)->default('MXN');
            $table->string('no_vuelo', 40)->nullable();
            $table->string('codigo', 50)->unique();
            $table->timestamps();

            $table->index(['id_rentadora', 'estado', 'fecha_inicio', 'fecha_fin'], 'res_busqueda_idx');
            $table->index(['estado', 'fecha_inicio', 'fecha_fin'], 'reservas_estado_fecha_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservaciones');
    }
};
