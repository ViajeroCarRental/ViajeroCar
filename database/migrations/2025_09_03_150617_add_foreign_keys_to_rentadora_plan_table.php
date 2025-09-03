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
        Schema::table('rentadora_plan', function (Blueprint $table) {
            $table->foreign(['id_plan'], 'rp_plan_fk')->references(['id_plan'])->on('planes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_rentadora'], 'rp_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentadora_plan', function (Blueprint $table) {
            $table->dropForeign('rp_plan_fk');
            $table->dropForeign('rp_rent_fk');
        });
    }
};
