<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('versiones', function (Blueprint $table) {
            $table->bigIncrements('id_version');
            $table->unsignedBigInteger('id_modelo');
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Unique por modelo+nombre
            $table->unique(['id_modelo', 'nombre'], 'uq_modelo_version');

            $table->foreign('id_modelo')
                ->references('id_modelo')->on('modelos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versiones');
    }
};
