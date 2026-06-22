<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seguro_individuales', function (Blueprint $table) {
            $table->bigIncrements('id_individual');
            $table->string('nombre', 150);
            $table->string('descripcion', 255)->nullable();
            
            // 💲 Precio general por día (Se usa si el seguro NO es de colisión/robo)
            $table->decimal('precio_por_dia', 10, 2)->default(0.00);

            // 🟢 1. COLUMNA AGREGADA: Conexión con la tabla de secciones
            // Saber si pertenece a: Robo y colisión, Gastos médicos o Asistencia en el camino
            $table->unsignedBigInteger('id_seccion');

            // 🟢 2. COLUMNA AGREGADA: Precios específicos por auto en formato JSON
            // Si es de Robo/Colisión, aquí se guardarán los montos del Word de golpe: {"C":500, "D":600, "E":700...}
            $table->json('precios_por_categoria')->nullable();

            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Índices y Llaves Foráneas
            $table->unique('nombre', 'individual_nombre_unique');
            $table->index('activo', 'individual_activo_index');
            
            // Relación física con la tabla de secciones
            $table->foreign('id_seccion')->references('id_seccion')->on('secciones_seguros')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguro_individuales');
    }
};