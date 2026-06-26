<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->bigIncrements('id_cliente');

            // 🔗 Relación con el usuario (de aquí viene el cliente)
            $table->unsignedBigInteger('id_usuario');

            // 👤 Tipo de cliente (Paso 1 de la vista)
            $table->enum('tipo_persona', ['fisica', 'moral', 'general']);

            // 📅 Datos personales comunes (física / general)
            $table->date('fecha_nacimiento')->nullable();

            // 🪪 Identificación
            $table->string('numero_identificacion', 100)->nullable();
            $table->string('tipo_identificacion', 30)->nullable()
                  ->comment('ine, pasaporte, cedula');

            // 🚗 Licencia
            $table->string('numero_licencia', 100)->nullable();
            $table->date('vigencia_licencia')->nullable();

            // 🏢 Empresa / marca (campos de la vista física)
            $table->string('numero_empresa', 100)->nullable();
            $table->string('nombre_empresa', 150)->nullable();

            // ⚙️ Estado
            $table->boolean('activo')->default(true);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // 📈 Índices
            $table->index('id_usuario', 'cli_usuario_idx');
            $table->index('tipo_persona', 'cli_tipo_idx');
            $table->index('activo', 'cli_activo_idx');

            // 🔗 FK a usuarios (NO se toca usuarios)
            $table->foreign('id_usuario')
                  ->references('id_usuario')->on('usuarios')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
