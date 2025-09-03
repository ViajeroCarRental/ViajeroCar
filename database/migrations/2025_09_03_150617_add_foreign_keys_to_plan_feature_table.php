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
        Schema::table('plan_feature', function (Blueprint $table) {
            $table->foreign(['id_feature'], 'pf_feature_fk')->references(['id_feature'])->on('features')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_plan'], 'pf_plan_fk')->references(['id_plan'])->on('planes')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_feature', function (Blueprint $table) {
            $table->dropForeign('pf_feature_fk');
            $table->dropForeign('pf_plan_fk');
        });
    }
};
