<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('talleres', function (Blueprint $table) {
            $table->bigIncrements('id_taller');
            $table->string('nombre', 150);
            $table->string('telefono', 25)->nullable();
            $table->string('contacto', 120)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talleres');
    }
};
