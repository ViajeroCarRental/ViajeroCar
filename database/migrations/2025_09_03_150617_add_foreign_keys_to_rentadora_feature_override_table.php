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
        Schema::table('rentadora_feature_override', function (Blueprint $table) {
            $table->foreign(['id_feature'], 'rfo_feat_fk')->references(['id_feature'])->on('features')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_rentadora'], 'rfo_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentadora_feature_override', function (Blueprint $table) {
            $table->dropForeign('rfo_feat_fk');
            $table->dropForeign('rfo_rent_fk');
        });
    }
};
