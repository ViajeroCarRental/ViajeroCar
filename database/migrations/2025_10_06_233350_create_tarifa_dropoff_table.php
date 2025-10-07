<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tarifa_dropoff', function (Blueprint $table) {
            $table->bigIncrements('id_dropoff');

            $table->unsignedBigInteger('id_ciudad_origen')->nullable();
            $table->unsignedBigInteger('id_sucursal_origen')->nullable();
            $table->unsignedBigInteger('id_ciudad_destino')->nullable();
            $table->unsignedBigInteger('id_sucursal_destino')->nullable();

            $table->enum('tipo_cobro', ['fijo','por_km'])->default('fijo');
            $table->decimal('monto_base', 10, 2)->default(0.00);
            $table->decimal('monto_por_km', 10, 2)->nullable();
            $table->string('moneda', 10)->default('MXN');
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['id_ciudad_origen'], 'td_origen_ciu_idx');
            $table->index(['id_sucursal_origen'], 'td_origen_suc_idx');
            $table->index(['id_ciudad_destino'], 'td_destino_ciu_idx');
            $table->index(['id_sucursal_destino'], 'td_destino_suc_idx');

            $table->foreign('id_ciudad_origen')
                ->references('id_ciudad')->on('ciudades')
                ->onDelete('restrict');

            $table->foreign('id_sucursal_origen')
                ->references('id_sucursal')->on('sucursales')
                ->onDelete('restrict');

            $table->foreign('id_ciudad_destino')
                ->references('id_ciudad')->on('ciudades')
                ->onDelete('restrict');

            $table->foreign('id_sucursal_destino')
                ->references('id_sucursal')->on('sucursales')
                ->onDelete('restrict');
        });

        // Checks de exclusividad origen/destino (exactamente uno en cada par)
        DB::statement("
            ALTER TABLE tarifa_dropoff
            ADD CONSTRAINT chk_td_origen
            CHECK (
                ((id_ciudad_origen IS NOT NULL) + (id_sucursal_origen IS NOT NULL)) = 1
            )
        ");
        DB::statement("
            ALTER TABLE tarifa_dropoff
            ADD CONSTRAINT chk_td_destino
            CHECK (
                ((id_ciudad_destino IS NOT NULL) + (id_sucursal_destino IS NOT NULL)) = 1
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifa_dropoff');
    }
};
