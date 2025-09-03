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
        Schema::create('cargo_adicional', function (Blueprint $table) {
            $table->bigIncrements('id_cargo');
            $table->unsignedBigInteger('id_rentadora')->index('cargo_rent_fk');
            $table->unsignedBigInteger('id_contrato')->index('cargo_ctr_idx');
            $table->unsignedBigInteger('id_concepto')->nullable()->index('cargo_concepto_idx');
            $table->string('concepto', 120);
            $table->decimal('monto', 10)->default(0);
            $table->string('moneda', 10)->default('MXN');
            $table->string('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargo_adicional');
    }
};
