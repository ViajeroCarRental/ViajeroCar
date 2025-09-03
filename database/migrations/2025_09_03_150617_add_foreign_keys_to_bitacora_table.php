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
        Schema::table('bitacora', function (Blueprint $table) {
            $table->foreign(['id_rentadora'], 'bitacora_rent_fk')->references(['id_rentadora'])->on('rentadoras')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'bitacora_usr_fk')->references(['id_usuario'])->on('usuarios')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitacora', function (Blueprint $table) {
            $table->dropForeign('bitacora_rent_fk');
            $table->dropForeign('bitacora_usr_fk');
        });
    }
};
