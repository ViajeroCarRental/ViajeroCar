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
        Schema::create('orden_mantenimiento_item', function (Blueprint $table) {
            $table->bigIncrements('id_item');
            $table->unsignedBigInteger('id_orden')->index('omi_orden_idx');
            $table->unsignedSmallInteger('id_tipo')->index('omi_tipo_fk');
            $table->string('descripcion')->nullable();
            $table->decimal('cantidad', 10)->default(1);
            $table->decimal('precio_unitario', 12)->default(0);
            $table->decimal('importe', 12)->nullable()->storedAs('(`cantidad` * `precio_unitario`)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_mantenimiento_item');
    }
};
