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
        Schema::table('usuario_rol', function (Blueprint $table) {
            $table->foreign(['id_rol'], 'usuario_rol_rol_fk')->references(['id_rol'])->on('roles')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'usuario_rol_usr_fk')->references(['id_usuario'])->on('usuarios')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuario_rol', function (Blueprint $table) {
            $table->dropForeign('usuario_rol_rol_fk');
            $table->dropForeign('usuario_rol_usr_fk');
        });
    }
};
