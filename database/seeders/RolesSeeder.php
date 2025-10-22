<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {

        $roles = [
            ['nombre' => 'SuperAdmin', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Flotilla',   'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ventas',     'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Usuario',    'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('roles')->insert($roles);
    }
}
