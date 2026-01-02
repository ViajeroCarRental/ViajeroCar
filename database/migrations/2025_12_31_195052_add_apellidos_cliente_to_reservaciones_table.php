<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservaciones', function (Blueprint $table) {
            $table->string('apellidos_cliente', 120)
                  ->nullable()
                  ->after('nombre_cliente');
        });
    }

    public function down(): void
    {
        Schema::table('reservaciones', function (Blueprint $table) {
            $table->dropColumn('apellidos_cliente');
        });
    }
};
