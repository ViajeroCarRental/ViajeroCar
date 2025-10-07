<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bloqueo_calendario', function (Blueprint $table) {
            $table->bigIncrements('id_bloqueo');

            $table->unsignedBigInteger('id_vehiculo');
            $table->enum('tipo', ['operativo','mantenimiento','logistica','otro'])->default('operativo');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->string('notas', 255)->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['id_vehiculo','fecha_inicio','fecha_fin'], 'bc_veh_idx');

            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('cascade');
        });

        // Check: fecha_fin > fecha_inicio
        DB::statement("
            ALTER TABLE bloqueo_calendario
            ADD CONSTRAINT chk_bc_fecha
            CHECK (fecha_fin > fecha_inicio)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('bloqueo_calendario');
    }
};
