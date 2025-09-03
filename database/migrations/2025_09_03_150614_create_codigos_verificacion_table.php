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
        Schema::create('codigos_verificacion', function (Blueprint $table) {
            $table->bigIncrements('id_codigo');
            $table->unsignedBigInteger('id_usuario')->index('cv_usuario_fk_idx');
            $table->string('codigo', 10);
            $table->dateTime('expira_en');
            $table->boolean('usado')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codigos_verificacion');
    }
};
