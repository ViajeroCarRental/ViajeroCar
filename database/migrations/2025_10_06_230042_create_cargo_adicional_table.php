<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cargo_adicional', function (Blueprint $table) {
            $table->bigIncrements('id_cargo');

            $table->unsignedBigInteger('id_contrato');
            $table->unsignedBigInteger('id_concepto')->nullable();

            $table->string('concepto', 120);
            $table->decimal('monto', 10, 2)->default(0.00);
            $table->string('moneda', 10)->default('MXN');

            $table->string('notas', 255)->nullable();

            /** 🔥 NUEVO: para gasolina, dropoff y variables */
            $table->json('detalle')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            /** Índices */
            $table->index('id_contrato', 'cargo_ctr_idx');
            $table->index('id_concepto', 'cargo_concepto_idx');

            /** Llaves foráneas */
            $table->foreign('id_contrato')
                ->references('id_contrato')->on('contratos')
                ->onDelete('cascade');

            $table->foreign('id_concepto')
                ->references('id_concepto')->on('cargo_concepto')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargo_adicional');
    }
};
