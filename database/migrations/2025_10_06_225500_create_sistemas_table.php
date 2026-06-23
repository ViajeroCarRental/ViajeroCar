<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sistemas', function (Blueprint $table) {
            $table->bigIncrements('id_sistema');
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->string('logo_url', 255)->nullable();
            $table->string('pasarela', 30)->nullable();
            $table->json('pasarela_config')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('activo', 'sistemas_activo_idx');
        });

        DB::table('sistemas')->insert([
            [
                'id_sistema' => 1,
                'codigo'     => 'VIAJERO',
                'nombre'     => 'Viajero Car Rental',
                'pasarela'   => 'stripe',
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_sistema' => 2,
                'codigo'     => 'ONEDREENG',
                'nombre'     => 'OneDreeng',
                'pasarela'   => 'stripe',
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sistemas');
    }
};