<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('seguros', function (Blueprint $table) {
            $table->id('id_seguro');
            $table->string('nombre', 120)->unique(); // Básico, Premium, Deducible Cero, etc.
            $table->string('cobertura', 255)->nullable(); // texto corto de cobertura
            $table->string('deducible', 120)->nullable(); // ej. "10%" o "0 MXN"
            $table->decimal('precio_por_dia', 10, 2)->default(0.00);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['activo']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('seguros');
    }
};
