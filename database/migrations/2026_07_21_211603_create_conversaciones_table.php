<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de conversaciones del agente de WhatsApp.
 *
 * Una fila por cada cliente que escribe (identificado por su teléfono).
 * Guarda el estado de la conversación y qué asesora la atiende, para que
 * si el cliente vuelve mañana lo atienda la misma persona.
 *
 * Los campos 'estado' y 'pausado_hasta' son los que hacen funcionar el
 * modo copiloto: cuando el administrador escribe desde su celular, la
 * conversación se pausa 3 minutos y el agente no responde.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversaciones', function (Blueprint $table) {
            $table->bigIncrements('id_conversacion');

            // Teléfono del cliente en formato internacional (ej. 5214421234567).
            // Es único: una sola conversación viva por número.
            $table->string('telefono', 30)->unique('conv_telefono_unq');

            // Nombre de la asesora asignada (Karina, Danna, Oliver...).
            // Se elige al crear la conversación y no cambia, para que el
            // cliente siempre hable con la misma persona.
            $table->string('nombre_agente', 40);

            // Nombre del cliente, si el agente lo llegó a saber.
            // Solo informativo, para poder revisar conversaciones.
            $table->string('nombre_cliente', 120)->nullable();

            // Estado de la conversación:
            //   activa         → el agente responde con normalidad
            //   pausada_admin  → un humano intervino; el agente calla hasta
            //                    que pase 'pausado_hasta'
            //   cerrada        → conversación archivada manualmente
            $table->enum('estado', ['activa', 'pausada_admin', 'cerrada'])
                  ->default('activa');

            // Hasta cuándo dura la pausa del modo copiloto.
            // NULL = no hay pausa. Si tiene fecha y ya pasó, el agente retoma.
            $table->dateTime('pausado_hasta')->nullable();

            // Marca de tiempo del último mensaje, de cualquiera de las partes.
            // Sirve para ordenar conversaciones y para la ventana de 24 horas
            // de WhatsApp (fuera de ella solo se pueden mandar plantillas).
            $table->dateTime('ultimo_mensaje_en')->nullable();

            // Timestamps con valor por defecto de MySQL, NO el timestamps()
            // normal de Laravel. La diferencia importa: estas tablas las
            // escribe SQLAlchemy desde la API de Python, que espera que MySQL
            // ponga la fecha. El timestamps() de Laravel crea las columnas sin
            // DEFAULT porque asume que Eloquent las va a llenar, y entonces
            // quedarían siempre en NULL.
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index('estado', 'conv_estado_idx');
            $table->index('ultimo_mensaje_en', 'conv_ultimo_msg_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversaciones');
    }
};
