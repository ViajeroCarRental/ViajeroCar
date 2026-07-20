<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato_revisiones', function (Blueprint $table) {
            $table->bigIncrements('id_revision');

            $table->unsignedBigInteger('id_contrato');
            $table->string('seccion', 40);

            $table->boolean('revisado')->default(true);
            $table->unsignedBigInteger('revisado_por')->nullable();
            $table->timestamp('revisado_en')->nullable();

            $table->timestamps();

            $table->unique(
                ['id_contrato', 'seccion'],
                'contrato_revisiones_contrato_seccion_unique'
            );

            $table->index('id_contrato');
            $table->index('revisado_por');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_revisiones');
    }
};