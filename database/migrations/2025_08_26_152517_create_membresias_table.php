<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('membresias', function (Blueprint $table) {
            $table->id('id_membresia');
            $table->string('nombre', 100)->unique(); // Ej: Oro, Platino, VIP
            $table->string('descripcion', 255)->nullable();
            $table->decimal('descuento_pct', 5, 2)->default(0.00); // porcentaje de descuento
            $table->string('condiciones', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('membresias');
    }
};
