<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            
            // 🆕 Agregar campo número de rin
            $table->string('numero_rin', 100)
                ->nullable()
                ->after('placa')
                ->comment('Número de rin del vehículo');

            // 🆕 Agregar campo capacidad del tanque
            $table->decimal('capacidad_tanque', 6, 2)
                ->nullable()
                ->after('numero_rin')
                ->comment('Capacidad del tanque en litros');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            // 🔄 Revertir cambios
            $table->dropColumn(['numero_rin', 'capacidad_tanque']);
            $table->string('vin', 100)->nullable();
            $table->unique('vin', 'vehiculos_vin_unique');
        });
    }
};
