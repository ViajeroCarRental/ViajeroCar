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
        Schema::table('orden_mantenimiento_item', function (Blueprint $table) {
            $table->foreign(['id_orden'], 'omi_orden_fk')->references(['id_orden'])->on('orden_mantenimiento')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_tipo'], 'omi_tipo_fk')->references(['id_tipo'])->on('mantenimiento_tipo')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_mantenimiento_item', function (Blueprint $table) {
            $table->dropForeign('omi_orden_fk');
            $table->dropForeign('omi_tipo_fk');
        });
    }
};
