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
        Schema::table('usuario_membresia', function (Blueprint $table) {
            $table->foreign(['id_membresia'], 'um_mem_fk')->references(['id_membresia'])->on('membresias')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'um_usr_fk')->references(['id_usuario'])->on('usuarios')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuario_membresia', function (Blueprint $table) {
            $table->dropForeign('um_mem_fk');
            $table->dropForeign('um_usr_fk');
        });
    }
};
