<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {

            // 🔹 Tipo de mantenimiento (mayor o menor)
            if (!Schema::hasColumn('mantenimientos', 'tipo_mantenimiento')) {
                $table->enum('tipo_mantenimiento', ['mayor', 'menor'])
                      ->nullable()
                      ->after('id_tipo');
            }

            // 🔹 Campo "otro" para especificar detalles adicionales
            if (!Schema::hasColumn('mantenimientos', 'otro')) {
                $table->string('otro', 255)
                      ->nullable()
                      ->after('observaciones');
            }

            // 🔹 Opciones específicas de mantenimiento menor
            if (!Schema::hasColumn('mantenimientos', 'rellenar_aceite')) {
                $table->boolean('rellenar_aceite')->default(false)->after('cambio_pastillas');
            }
            if (!Schema::hasColumn('mantenimientos', 'nivel_agua')) {
                $table->boolean('nivel_agua')->default(false)->after('rellenar_aceite');
            }
            if (!Schema::hasColumn('mantenimientos', 'presion_llantas')) {
                $table->boolean('presion_llantas')->default(false)->after('nivel_agua');
            }
            if (!Schema::hasColumn('mantenimientos', 'limpieza_general')) {
                $table->boolean('limpieza_general')->default(false)->after('presion_llantas');
            }
        });
    }

    public function down(): void
{
    Schema::table('mantenimientos', function (Blueprint $table) {
        if (Schema::hasColumn('mantenimientos', 'tipo_mantenimiento')) {
            $table->dropColumn('tipo_mantenimiento');
        }
        if (Schema::hasColumn('mantenimientos', 'otro')) {
            $table->dropColumn('otro');
        }
        if (Schema::hasColumn('mantenimientos', 'rellenar_aceite')) {
            $table->dropColumn('rellenar_aceite');
        }
        if (Schema::hasColumn('mantenimientos', 'nivel_agua')) {
            $table->dropColumn('nivel_agua');
        }
        if (Schema::hasColumn('mantenimientos', 'presion_llantas')) {
            $table->dropColumn('presion_llantas');
        }
        if (Schema::hasColumn('mantenimientos', 'limpieza_general')) {
            $table->dropColumn('limpieza_general');
        }
    });
}

};
