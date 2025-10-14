<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        $this->call([
        CiudadesSeeder::class,
        SucursalesSeeder::class,
        CategoriasCarrosSeeder::class,
        EstatusCarroSeeder::class,
        MarcasSeeder::class,
        ModelosSeeder::class,
        VersionesSeeder::class,
        VehiculosSeeder::class,
        VehiculoImagenesSeeder::class,
        ServiciosSeeder::class, // <-- aquí]);
    ]);

    }
}
