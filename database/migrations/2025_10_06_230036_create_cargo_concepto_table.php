<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cargo_concepto', function (Blueprint $table) {
            $table->bigIncrements('id_concepto');
            $table->string('clave', 40)->unique();
            $table->string('nombre', 120);
            $table->string('descripcion', 255)->nullable();
            $table->decimal('monto_base', 10, 2)->nullable();
            $table->string('moneda', 10)->default('MXN');
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargo_concepto');
    }
};
