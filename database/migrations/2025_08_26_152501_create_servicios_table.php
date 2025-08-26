<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id('id_servicio');
            $table->string('nombre', 120)->unique(); // GPS, silla bebé, conductor adicional
            $table->string('descripcion', 255)->nullable();
            $table->enum('tipo_cobro', ['por_dia', 'por_evento'])->default('por_dia');
            $table->decimal('precio', 10, 2)->default(0.00);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['tipo_cobro', 'activo']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('servicios');
    }
};
