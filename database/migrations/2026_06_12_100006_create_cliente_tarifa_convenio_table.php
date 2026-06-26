<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cliente_tarifa_convenio', function (Blueprint $table) {
            $table->bigIncrements('id_tarifa_convenio');

            $table->unsignedBigInteger('id_cliente');

            // 🚗 Categoría (solo identifica de qué categoría es; el precio vive aquí, NO se lee de categorias_carros)
            $table->unsignedBigInteger('id_categoria');

            // 💰 Tarifas NEGOCIADAS (se precargan del catálogo pero son editables y viven aisladas aquí)
            $table->decimal('tarifa_diaria', 10, 2)->default(0.00);
            $table->decimal('tarifa_semanal', 10, 2)->default(0.00);
            $table->decimal('tarifa_mensual', 10, 2)->default(0.00);

            // 🛡️ Paquete de protección elegido (seguro_paquete)
            $table->unsignedBigInteger('id_paquete')->nullable();

            // 🧊 Precio del paquete CONGELADO al momento de firmar (snapshot)
            $table->string('paquete_nombre', 150)->nullable();
            $table->decimal('paquete_precio_dia', 10, 2)->nullable();

            // 🧮 Total diario calculado (tarifa_diaria + paquete_precio_dia)
            $table->decimal('total_diario', 10, 2)->default(0.00);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_cliente', 'ctar_cliente_idx');
            $table->index('id_categoria', 'ctar_categoria_idx');
            $table->index('id_paquete', 'ctar_paquete_idx');

            // 🔗 FK al cliente
            $table->foreign('id_cliente')
                  ->references('id_cliente')->on('clientes')
                  ->onDelete('cascade');

            // 🔗 FK a categorias_carros (solo lectura, NO se toca)
            $table->foreign('id_categoria')
                  ->references('id_categoria')->on('categorias_carros')
                  ->onDelete('cascade');

            // 🔗 FK a seguro_paquete (solo lectura, NO se toca)
            $table->foreign('id_paquete')
                  ->references('id_paquete')->on('seguro_paquete')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_tarifa_convenio');
    }
};
