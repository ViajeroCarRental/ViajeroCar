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
        Schema::create('rentadora_feature_override', function (Blueprint $table) {
            $table->unsignedBigInteger('id_rentadora');
            $table->unsignedBigInteger('id_feature')->index('rfo_feat_fk');
            $table->boolean('habilitado');

            $table->primary(['id_rentadora', 'id_feature']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentadora_feature_override');
    }
};
