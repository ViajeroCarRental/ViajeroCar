<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->bigIncrements('id_gasto');

            // 🔗 Relación con vehículo
            $table->unsignedBigInteger('id_vehiculo');

            // 💡 Tipo y descripción del gasto
            $table->string('tipo', 50);
            $table->string('descripcion', 255)->nullable();

            // 💰 Monto y fecha
            $table->decimal('monto', 10, 2)->default(0.00);

            // ✅ Default fijo como en tu tabla real
            $table->date('fecha')->default('2025-12-12');

            // ✅ Timestamps nullable (como tu tabla real)
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Índices (por el MUL)
            $table->index('id_vehiculo', 'gastos_veh_idx');

            // 🔗 FK vehículo
            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
