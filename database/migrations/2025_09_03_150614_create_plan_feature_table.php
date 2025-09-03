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
        Schema::create('plan_feature', function (Blueprint $table) {
            $table->unsignedBigInteger('id_plan');
            $table->unsignedBigInteger('id_feature')->index('pf_feature_fk');

            $table->primary(['id_plan', 'id_feature']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_feature');
    }
};
