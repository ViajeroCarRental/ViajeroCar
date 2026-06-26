<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('responsiva_clausula', function (Blueprint $table) {
            $table->bigIncrements('id_responsiva_clausula');

            $table->unsignedBigInteger('id_responsiva');

            // 📜 Texto de la cláusula de la responsiva
            $table->text('texto');

            // 🔢 Orden de aparición
            $table->integer('orden')->default(0);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_responsiva', 'rclau_responsiva_idx');

            $table->foreign('id_responsiva')
                  ->references('id_responsiva')->on('convenio_responsiva')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responsiva_clausula');
    }
};
