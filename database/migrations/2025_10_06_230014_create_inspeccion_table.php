<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inspeccion', function (Blueprint $table) {
            $table->bigIncrements('id_inspeccion');
            $table->unsignedBigInteger('id_contrato');
            $table->enum('tipo', ['salida','entrada']);
            $table->dateTime('fecha')->useCurrent();
            $table->integer('odometro_km');
            $table->decimal('nivel_combustible', 5, 2)->nullable();
            $table->string('firma_cliente_url', 300)->nullable();
            $table->string('observaciones', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['id_contrato','tipo'], 'insp_ctr_idx');

            $table->foreign('id_contrato')
                ->references('id_contrato')->on('contratos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspeccion');
    }
};
