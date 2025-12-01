<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seguro_individuales', function (Blueprint $table) {
            $table->bigIncrements('id_individual');
            $table->string('nombre', 150);
            $table->string('descripcion', 255)->nullable();
            $table->decimal('precio_por_dia', 10, 2)->default(0.00);
            $table->boolean('activo')->default(true);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('nombre', 'individual_nombre_unique');
            $table->index('activo', 'individual_activo_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguro_individuales');
    }
};
