<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiculoImagenesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('vehiculo_imagenes')->insert([
            // 1️⃣ Económico — Chevrolet Aveo o similar
            [
                'id_vehiculo' => 1,
                'url' => 'https://images.unsplash.com/photo-1619767886558-efdc259cde1a?q=80&w=1200&auto=format&fit=crop',
                'mime_type' => 'image/jpeg',
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 2️⃣ Compacto — Nissan March o similar
            [
                'id_vehiculo' => 2,
                'url' => 'https://images.unsplash.com/photo-1571066811602-716837d681de?q=80&w=1200&auto=format&fit=crop',
                'mime_type' => 'image/jpeg',
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 3️⃣ Sedán — Volkswagen Virtus o similar
            [
                'id_vehiculo' => 3,
                'url' => 'https://images.unsplash.com/photo-1606661421950-0f23d4f9f4ce?q=80&w=1200&auto=format&fit=crop',
                'mime_type' => 'image/jpeg',
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 4️⃣ SUV — Kia Sportage o similar
            [
                'id_vehiculo' => 4,
                'url' => 'https://images.unsplash.com/photo-1603380355075-45a9cd9bba56?q=80&w=1200&auto=format&fit=crop',
                'mime_type' => 'image/jpeg',
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 5️⃣ Pickup — Ford Ranger o similar
            [
                'id_vehiculo' => 5,
                'url' => 'https://images.unsplash.com/photo-1605559424843-9e4c2b90e3c6?q=80&w=1200&auto=format&fit=crop',
                'mime_type' => 'image/jpeg',
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 6️⃣ De lujo — BMW Serie 3 o similar
            [
                'id_vehiculo' => 6,
                'url' => 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?q=80&w=1200&auto=format&fit=crop',
                'mime_type' => 'image/jpeg',
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 7️⃣ Deportivos — Mazda MX-5 o similar
            [
                'id_vehiculo' => 7,
                'url' => 'https://images.unsplash.com/photo-1622193580197-5e29e3c5f417?q=80&w=1200&auto=format&fit=crop',
                'mime_type' => 'image/jpeg',
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 8️⃣ Híbridos — Toyota Prius o similar
            [
                'id_vehiculo' => 8,
                'url' => 'https://images.unsplash.com/photo-1608025523641-c64aa8f9e3ab?q=80&w=1200&auto=format&fit=crop',
                'mime_type' => 'image/jpeg',
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 9️⃣ Eléctricos — Tesla Model 3 o similar
            [
                'id_vehiculo' => 9,
                'url' => 'https://images.unsplash.com/photo-1619767886931-3a4690b5b8b8?q=80&w=1200&auto=format&fit=crop',
                'mime_type' => 'image/jpeg',
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 🔟 Vans — Mercedes-Benz Clase V o similar
            [
                'id_vehiculo' => 10,
                'url' => 'https://images.unsplash.com/photo-1597008855884-58d9a0a97f4c?q=80&w=1200&auto=format&fit=crop',
                'mime_type' => 'image/jpeg',
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
