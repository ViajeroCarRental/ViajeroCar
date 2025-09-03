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
        Schema::create('bitacora', function (Blueprint $table) {
            $table->bigIncrements('id_log');
            $table->unsignedBigInteger('id_rentadora')->index('bitacora_rentadora_idx');
            $table->unsignedBigInteger('id_usuario')->nullable()->index('bitacora_usr_fk');
            $table->string('entidad_tipo', 50);
            $table->unsignedBigInteger('entidad_id');
            $table->enum('accion', ['INSERT', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'OTRO']);
            $table->json('detalle')->nullable();
            $table->string('ip_origen', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('creado_en')->useCurrent();

            $table->index(['entidad_tipo', 'entidad_id'], 'bitacora_entidad_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
