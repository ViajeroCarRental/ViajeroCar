<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarcasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('marcas')->insert([
            ['nombre' => 'Chevrolet',      'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Volkswagen',     'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Kia',            'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'BMW',            'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Nissan',         'created_at' => now(), 'updated_at' => now()], // compacto/económico
            ['nombre' => 'Toyota',         'created_at' => now(), 'updated_at' => now()], // sedán/híbridos
            ['nombre' => 'Ford',           'created_at' => now(), 'updated_at' => now()], // pickups
            ['nombre' => 'Tesla',          'created_at' => now(), 'updated_at' => now()], // eléctricos
            ['nombre' => 'Mercedes-Benz',  'created_at' => now(), 'updated_at' => now()], // vans/lujo
            ['nombre' => 'Mazda',          'created_at' => now(), 'updated_at' => now()], // deportivos (MX-5)
        ]);
    }
}
