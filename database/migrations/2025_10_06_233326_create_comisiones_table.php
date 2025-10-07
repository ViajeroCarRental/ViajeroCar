<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comisiones', function (Blueprint $table) {
            $table->bigIncrements('id_comision');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_contrato')->nullable();
            $table->unsignedBigInteger('id_reservacion')->nullable();
            $table->decimal('monto', 10, 2)->default(0.00);
            $table->string('moneda', 10)->default('MXN');
            $table->enum('estado', ['pendiente','pagada','cancelada'])->default('pendiente');
            $table->string('notas', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_usuario', 'com_usr_idx');
            $table->index('id_contrato', 'com_ctr_idx');
            $table->index('id_reservacion', 'com_res_idx');

            $table->foreign('id_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onDelete('cascade');

            $table->foreign('id_contrato')
                ->references('id_contrato')->on('contratos')
                ->onDelete('set null');

            $table->foreign('id_reservacion')
                ->references('id_reservacion')->on('reservaciones')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comisiones');
    }
};
