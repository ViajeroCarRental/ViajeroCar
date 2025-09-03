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
        Schema::create('comisiones', function (Blueprint $table) {
            $table->bigIncrements('id_comision');
            $table->unsignedBigInteger('id_usuario')->index('com_usr_idx');
            $table->unsignedBigInteger('id_contrato')->nullable()->index('com_ctr_idx');
            $table->unsignedBigInteger('id_reservacion')->nullable()->index('com_res_idx');
            $table->decimal('monto', 10)->default(0);
            $table->string('moneda', 10)->default('MXN');
            $table->enum('estado', ['pendiente', 'pagada', 'cancelada'])->default('pendiente');
            $table->string('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comisiones');
    }
};
