<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sucursales', function (Blueprint $table) {
            $table->bigIncrements('id_sucursal');

            $table->unsignedBigInteger('id_ciudad');
            $table->string('nombre', 120);
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lng', 9, 6)->nullable();
            $table->json('horario_json')->nullable();

            // 🆕 Fotos de la oficina (se crean como binary; abajo se pasan a LONGBLOB)
            $table->binary('imagen_1')->nullable();
            $table->binary('imagen_2')->nullable();

            // 🆕 URL de la dirección (link de Google Maps, etc.)
            $table->string('url_direccion', 500)->nullable();

            // 🆕 Permisos de visibilidad (default true: las sucursales
            // quedan visibles en usuario y admin por defecto)
            $table->boolean('ver_usuario')->default(true);
            $table->boolean('ver_admin')->default(true);

            $table->boolean('activo')->default(true);

            // ✅ timestamps nullable (espejo del DESCRIBE)
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Unicidad por ciudad + nombre (igual que lo tenías)
            $table->unique(['id_ciudad','nombre'], 'sucursales_ciudad_nombre_unique');

            // Índices
            $table->index('id_ciudad', 'suc_ciudad_idx');
            $table->index('activo', 'suc_activo_idx');

            // FK
            $table->foreign('id_ciudad')
                ->references('id_ciudad')->on('ciudades')
                ->onDelete('cascade');
        });

        // 🔥 Forzamos LONGBLOB en las imágenes (binary de Laravel no lo garantiza)
        DB::statement('ALTER TABLE sucursales MODIFY imagen_1 LONGBLOB NULL');
        DB::statement('ALTER TABLE sucursales MODIFY imagen_2 LONGBLOB NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
