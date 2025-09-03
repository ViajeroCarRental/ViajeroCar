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
        Schema::table('usuario_facturacion', function (Blueprint $table) {
            $table->foreign(['id_rentadora'], 'uf_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'uf_usr_fk')->references(['id_usuario'])->on('usuarios')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuario_facturacion', function (Blueprint $table) {
            $table->dropForeign('uf_rent_fk');
            $table->dropForeign('uf_usr_fk');
        });
    }
};
