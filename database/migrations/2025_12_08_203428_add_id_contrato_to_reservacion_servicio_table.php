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
    Schema::table('reservacion_servicio', function (Blueprint $table) {
        if (!Schema::hasColumn('reservacion_servicio', 'id_contrato')) {

            // Agregamos la columna
            $table->unsignedBigInteger('id_contrato')->nullable()->after('id_servicio');

            // Agregamos la FK
            $table->foreign('id_contrato')
                ->references('id_contrato')->on('contratos')
                ->onDelete('cascade');
        }
    });
}

public function down(): void
{
    Schema::table('reservacion_servicio', function (Blueprint $table) {
        if (Schema::hasColumn('reservacion_servicio', 'id_contrato')) {

            $table->dropForeign(['id_contrato']);
            $table->dropColumn('id_contrato');
        }
    });
}

};
