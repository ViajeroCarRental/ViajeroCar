<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de mensajes de las conversaciones del agente.
 *
 * Guarda TODO lo que pasa en una conversación, incluidos los bloques
 * técnicos de uso de herramientas. Eso es a propósito: Claude necesita
 * el historial completo para funcionar. Si se guardara solo el texto
 * legible, un tool_use quedaría sin su tool_result y la API devolvería
 * error al recargar la conversación.
 *
 * Para leer solo lo que un humano entendería, se filtra por tipo='texto'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->bigIncrements('id_mensaje');

            $table->unsignedBigInteger('id_conversacion');

            // Quién habló, desde el punto de vista del negocio:
            //   cliente → la persona que escribe por WhatsApp
            //   agente  → la IA
            //   admin   → un humano que intervino desde el celular
            //
            // El valor 'admin' es clave para el modo copiloto: cuando el
            // agente retoma, lee lo que escribió el humano y se adapta en
            // vez de contradecirlo.
            $table->enum('rol', ['cliente', 'agente', 'admin']);

            // Qué es esta fila, desde el punto de vista técnico:
            //   texto → un mensaje real de la conversación
            //   tool  → un bloque de uso de herramienta (tool_use/tool_result)
            //
            // Sirve para poder leer la conversación sin el ruido técnico.
            $table->enum('tipo', ['texto', 'tool'])->default('texto');

            // El rol tal como lo espera la API de Anthropic ('user' o
            // 'assistant'). Se guarda aparte del rol de negocio porque no
            // siempre coinciden: un tool_result va como 'user' aunque no lo
            // haya escrito el cliente, y un mensaje del admin va como
            // 'assistant' aunque no lo haya escrito la IA.
            $table->enum('rol_api', ['user', 'assistant']);

            // El contenido, serializado como JSON. Puede ser un texto simple
            // o una lista de bloques (tool_use, tool_result, texto). Se guarda
            // en JSON para poder reconstruir el historial exactamente como
            // estaba, sin importar su forma.
            $table->json('contenido');

            // Copia en texto plano de lo que se dijo, cuando aplica.
            // Es redundante con 'contenido', pero permite leer y buscar
            // conversaciones sin tener que parsear JSON. NULL en las filas
            // de tipo 'tool'.
            $table->text('texto_plano')->nullable();

            // Timestamps con valor por defecto de MySQL (ver nota en la
            // migración de conversaciones): estas tablas las escribe
            // SQLAlchemy, no Eloquent, así que el DEFAULT tiene que estar
            // en la tabla misma.
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_conversacion', 'msj_conversacion_fk')
                  ->references('id_conversacion')
                  ->on('conversaciones')
                  ->onDelete('cascade');

            // Índice compuesto: así se cargan los últimos N mensajes de una
            // conversación en una sola pasada, que es la consulta más
            // frecuente del sistema.
            $table->index(['id_conversacion', 'id_mensaje'], 'msj_conv_orden_idx');
            $table->index('rol', 'msj_rol_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
