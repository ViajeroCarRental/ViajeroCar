<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contrato_evento', function (Blueprint $table) {
            $table->bigIncrements('id_evento');
            $table->unsignedBigInteger('id_contrato');
            $table->string('evento', 120);
            $table->json('detalle')->nullable();
            $table->unsignedBigInteger('realizado_por')->nullable();
            $table->dateTime('realizado_en')->useCurrent();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_contrato', 'ctr_evt_ctr_idx');

            $table->foreign('id_contrato')
                ->references('id_contrato')->on('contratos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_evento');
    }
};
