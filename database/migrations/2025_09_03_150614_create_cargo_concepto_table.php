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
        Schema::create('cargo_concepto', function (Blueprint $table) {
            $table->bigIncrements('id_concepto');
            $table->unsignedBigInteger('id_rentadora')->index('cc_rent_idx');
            $table->string('clave', 40);
            $table->string('nombre', 120);
            $table->string('descripcion')->nullable();
            $table->decimal('monto_base', 10)->nullable();
            $table->string('moneda', 10)->nullable()->default('MXN');
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();

            $table->unique(['id_rentadora', 'clave'], 'cc_unq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargo_concepto');
    }
};
