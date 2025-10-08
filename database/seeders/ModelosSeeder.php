<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModelosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('modelos')->insert([
            // 1. Chevrolet (Económico)
            ['id_marca' => 1, 'nombre' => 'Aveo', 'created_at' => now(), 'updated_at' => now()],

            // 2. Volkswagen (Sedán)
            ['id_marca' => 2, 'nombre' => 'Virtus', 'created_at' => now(), 'updated_at' => now()],

            // 3. Kia (SUV)
            ['id_marca' => 3, 'nombre' => 'Sportage', 'created_at' => now(), 'updated_at' => now()],

            // 4. BMW (De lujo)
            ['id_marca' => 4, 'nombre' => 'Serie 3', 'created_at' => now(), 'updated_at' => now()],

            // 5. Nissan (Compacto)
            ['id_marca' => 5, 'nombre' => 'March', 'created_at' => now(), 'updated_at' => now()],

            // 6. Toyota (Híbrido)
            ['id_marca' => 6, 'nombre' => 'Prius', 'created_at' => now(), 'updated_at' => now()],

            // 7. Ford (Pickup)
            ['id_marca' => 7, 'nombre' => 'Ranger', 'created_at' => now(), 'updated_at' => now()],

            // 8. Tesla (Eléctrico)
            ['id_marca' => 8, 'nombre' => 'Model 3', 'created_at' => now(), 'updated_at' => now()],

            // 9. Mercedes-Benz (Van)
            ['id_marca' => 9, 'nombre' => 'Clase V', 'created_at' => now(), 'updated_at' => now()],

            // 10. Mazda (Deportivo)
            ['id_marca' => 10, 'nombre' => 'MX-5', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
