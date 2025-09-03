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
        Schema::create('usuario_membresia', function (Blueprint $table) {
            $table->bigIncrements('id_usuario_membresia');
            $table->unsignedBigInteger('id_usuario')->index('um_usr_fk_idx');
            $table->unsignedBigInteger('id_membresia')->index('um_mem_fk_idx');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_membresia');
    }
};
