<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siniestros', function (Blueprint $table) {
            $table->bigIncrements('id_siniestro');

            $table->unsignedBigInteger('id_vehiculo');

            $table->string('folio', 50)->unique();
            $table->date('fecha'); // ✅ sin default fijo

            $table->enum('tipo', [
                'Recuperado',
                'Robo',
                'Robo de piezas',
                'Pérdida total',
                'Temas legales'
            ]);

            // ✅ faltaba
            $table->text('descripcion')->nullable();

            $table->string('estatus', 50)->default('Abierto');

            $table->decimal('deducible', 10, 2)->nullable();
            $table->string('rin', 100)->nullable();

            $table->string('archivo', 255)->nullable();

            // ✅ timestamps nullable (para espejo exacto del DESCRIBE)
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_vehiculo', 'sin_veh_idx');

            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siniestros');
    }
};
