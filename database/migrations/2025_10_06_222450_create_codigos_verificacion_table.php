<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('codigos_verificacion', function (Blueprint $table) {
            $table->bigIncrements('id_codigo');
            $table->unsignedBigInteger('id_usuario');
            $table->string('codigo', 10);
            $table->dateTime('expira_en');
            $table->boolean('usado')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('id_usuario', 'cv_usuario_fk_idx');
            $table->foreign('id_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_verificacion');
    }
};
