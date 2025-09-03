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
        Schema::create('rentadora_plan', function (Blueprint $table) {
            $table->unsignedBigInteger('id_rentadora');
            $table->unsignedBigInteger('id_plan')->index('rp_plan_fk');
            $table->date('activo_desde');
            $table->date('activo_hasta')->nullable();

            $table->primary(['id_rentadora', 'id_plan', 'activo_desde']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentadora_plan');
    }
};
