<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('modelos', function (Blueprint $table) {
            $table->bigIncrements('id_modelo');
            $table->unsignedBigInteger('id_marca');
            $table->string('nombre', 100);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Unique por marca+nombre
            $table->unique(['id_marca', 'nombre'], 'uq_marca_modelo');

            $table->foreign('id_marca')
                ->references('id_marca')->on('marcas')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modelos');
    }
};
