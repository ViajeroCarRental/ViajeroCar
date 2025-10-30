<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            if (!Schema::hasColumn('gastos', 'id_carroceria')) {
                $table->unsignedBigInteger('id_carroceria')->nullable()->after('id_vehiculo');

                $table->foreign('id_carroceria')
                    ->references('id_carroceria')
                    ->on('carrocerias')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->dropForeign(['id_carroceria']);
            $table->dropColumn('id_carroceria');
        });
    }
};
