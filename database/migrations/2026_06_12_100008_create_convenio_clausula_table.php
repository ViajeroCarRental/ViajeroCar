<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('convenio_clausula', function (Blueprint $table) {
            $table->bigIncrements('id_clausula');

            $table->unsignedBigInteger('id_convenio');

            // 📜 Texto de la cláusula
            $table->text('texto');

            // 🔢 Orden de aparición
            $table->integer('orden')->default(0);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_convenio', 'cclau_convenio_idx');

            $table->foreign('id_convenio')
                  ->references('id_convenio')->on('convenios')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_clausula');
    }
};
