<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de mensajes de las conversaciones del agente.
 * Guarda TODO, incluidos los bloques de herramientas: Claude necesita el
 * historial completo. Para leer solo lo humano, filtrar por tipo='texto'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->bigIncrements('id_mensaje');

            $table->unsignedBigInteger('id_conversacion');

            // Quién habló: cliente (persona), agente (IA), admin (humano en copiloto).
            $table->enum('rol', ['cliente', 'agente', 'admin']);

            // Qué es la fila: mensaje real o bloque técnico de herramienta.
            $table->enum('tipo', ['texto', 'tool'])->default('texto');

            // Rol que espera Anthropic. No siempre coincide con 'rol': un tool_result
            // va como 'user' y un mensaje del admin va como 'assistant'.
            $table->enum('rol_api', ['user', 'assistant']);

            // Contenido serializado. Texto simple o lista de bloques (tool_use, etc).
            $table->json('contenido');

            // Copia legible, para leer sin parsear JSON. NULL en filas de tipo 'tool'.
            $table->text('texto_plano')->nullable();

            // ID del mensaje en WhatsApp. Distingue lo que mandó el bot de lo que
            // escribió un humano: sin esto, el bot se pausaría a sí mismo al responder.
            $table->string('wamid', 128)->nullable();

            // Timestamps con DEFAULT de MySQL: quien escribe es SQLAlchemy, no Eloquent.
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_conversacion', 'msj_conversacion_fk')
                  ->references('id_conversacion')
                  ->on('conversaciones')
                  ->onDelete('cascade');

            // Índice compuesto: carga los últimos N mensajes de una conversación.
            $table->index(['id_conversacion', 'id_mensaje'], 'msj_conv_orden_idx');
            $table->index('rol', 'msj_rol_idx');

            // Se consulta en cada echo que llega, para saber quién mandó el mensaje.
            $table->index('wamid', 'msj_wamid_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
