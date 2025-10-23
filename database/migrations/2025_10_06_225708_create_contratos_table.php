<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->bigIncrements('id_contrato');

            $table->unsignedBigInteger('id_reservacion');
            $table->unsignedBigInteger('id_asesor')->nullable();

            $table->string('numero_contrato', 60)->unique();
            $table->enum('estado', ['abierto','cerrado','cancelado'])->default('abierto');
            $table->dateTime('abierto_en')->nullable();
            $table->dateTime('cerrado_en')->nullable();
            $table->string('motivo_apertura_anticipada', 255)->nullable();
            $table->string('motivo_cierre_anticipado', 255)->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_reservacion', 'contrato_res_idx');
            $table->index('id_asesor', 'ctr_asesor_idx');

            $table->foreign('id_reservacion')
                ->references('id_reservacion')->on('reservaciones')
                ->onDelete('cascade');

            $table->foreign('id_asesor')
                ->references('id_usuario')->on('usuarios')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
