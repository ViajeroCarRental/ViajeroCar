<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->bigIncrements('id_notificacion');
            $table->string('tipo', 80);
            $table->string('canal', 20);
            $table->string('destinatario', 150)->nullable();
            $table->dateTime('programada_en')->nullable();
            $table->dateTime('enviada_en')->nullable();
            $table->enum('estado', ['queued','sent','failed'])->default('queued');
            $table->json('payload_json')->nullable();
            $table->string('entidad_tipo', 50)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
