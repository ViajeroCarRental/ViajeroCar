<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id('id_vehiculo');
            $table->foreignId('id_ciudad')
                  ->constrained('ciudades', 'id_ciudad')
                  ->onDelete('cascade');
            $table->foreignId('id_categoria')
                  ->constrained('categorias_carros', 'id_categoria')
                  ->onDelete('cascade');
            $table->foreignId('id_estatus')
                  ->constrained('estatus_carro', 'id_estatus')
                  ->onDelete('cascade');

            $table->string('marca', 100);
            $table->string('modelo', 100);
            $table->year('anio');
            $table->string('nombre_publico', 150);
            $table->string('transmision', 50)->nullable(); // automática / manual
            $table->string('combustible', 50)->nullable(); // gasolina / diesel / eléctrico
            $table->integer('asientos')->default(4);
            $table->integer('puertas')->default(4);
            $table->integer('kilometraje')->default(0);
            $table->decimal('precio_dia', 10, 2)->default(0.00);
            $table->decimal('deposito_garantia', 10, 2)->default(0.00);
            $table->string('placa', 50)->unique()->nullable();
            $table->string('vin', 100)->unique()->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();

            $table->index(['marca', 'modelo', 'anio']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('vehiculos');
    }
};
