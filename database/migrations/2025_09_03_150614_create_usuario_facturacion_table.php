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
        Schema::create('usuario_facturacion', function (Blueprint $table) {
            $table->bigIncrements('id_usuario_facturacion');
            $table->unsignedBigInteger('id_rentadora');
            $table->unsignedBigInteger('id_usuario')->index('uf_usr_fk');
            $table->string('rfc', 13);
            $table->string('razon_social', 200);
            $table->string('domicilio')->nullable();
            $table->string('uso_cfdi', 5)->nullable();
            $table->string('pais', 60)->nullable()->default('México');
            $table->boolean('predeterminado')->default(false);
            $table->timestamps();

            $table->unique(['id_rentadora', 'id_usuario', 'rfc'], 'uf_usr_rent_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_facturacion');
    }
};
