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
        Schema::table('reservaciones', function (Blueprint $table) {
            // Fecha de nacimiento del titular. Se usa para calcular la edad
            // y aplicar el cargo de conductor menor (18-24 años).
            // Nullable porque las reservaciones existentes no tienen este dato.
            $table->date('fecha_nacimiento')->nullable()->after('telefono_cliente');
        });
    }

    public function down(): void
    {
        Schema::table('reservaciones', function (Blueprint $table) {
            $table->dropColumn('fecha_nacimiento');
        });
    }
};
