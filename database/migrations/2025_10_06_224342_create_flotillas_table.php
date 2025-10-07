<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flotillas', function (Blueprint $table) {
            $table->bigIncrements('id_flotilla');
            $table->string('nombre', 120);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('nombre', 'flotillas_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flotillas');
    }
};
