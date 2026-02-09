<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicaciones_servicio', function (Blueprint $table) {
            $table->bigIncrements('id_ubicacion');

            $table->string('estado', 100);
            $table->string('destino', 200);
            $table->integer('km');

            $table->boolean('activo')->default(true);

            // ✅ nullable como tu DESCRIBE
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones_servicio');
    }
};
