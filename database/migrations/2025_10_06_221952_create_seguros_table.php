<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seguros', function (Blueprint $table) {
            $table->bigIncrements('id_seguro');
            $table->string('nombre', 120)->unique();
            $table->string('cobertura', 255)->nullable();
            $table->string('deducible', 120)->nullable();
            $table->decimal('precio_por_dia', 10, 2)->default(0.00);
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('activo', 'seguros_activo_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguros');
    }
};
