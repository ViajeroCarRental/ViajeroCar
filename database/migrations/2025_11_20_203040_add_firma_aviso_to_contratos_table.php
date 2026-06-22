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
    Schema::table('contratos', function (Blueprint $table) {
        // 👉 NUEVA COLUMNA PARA LA FIRMA DEL AVISO (BASE64)
        $table->longText('firma_aviso')->nullable()->after('firma_recibio');
    });
}

public function down(): void
{
    Schema::table('contratos', function (Blueprint $table) {
        $table->dropColumn('firma_aviso');
    });
}

};
