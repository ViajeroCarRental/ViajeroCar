<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('estatus_carro', function (Blueprint $table) {
            $table->bigIncrements('id_estatus');
            $table->string('nombre', 80)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estatus_carro');
    }
};
