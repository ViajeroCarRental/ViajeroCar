<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('convenio_responsiva', function (Blueprint $table) {
            $table->bigIncrements('id_responsiva');

            $table->unsignedBigInteger('id_convenio');

            // 🔗 Conductor al que pertenece la responsiva
            $table->unsignedBigInteger('id_conductor_convenio');

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_convenio', 'cresp_convenio_idx');
            $table->index('id_conductor_convenio', 'cresp_conductor_idx');

            $table->foreign('id_convenio')
                  ->references('id_convenio')->on('convenios')
                  ->onDelete('cascade');

            $table->foreign('id_conductor_convenio')
                  ->references('id_conductor_convenio')->on('conductor_adicional_convenio')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_responsiva');
    }
};
