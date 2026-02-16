<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->bigIncrements('id_servicio');
            $table->string('nombre', 120)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->enum('tipo_cobro', ['por_dia', 'por_evento'])->default('por_dia'); //Diferencia de entre por dia y por evento.
            $table->decimal('precio', 10, 2)->default(0.00);
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['tipo_cobro', 'activo'], 'servicios_tipo_cobro_activo_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
