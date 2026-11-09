<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Dish;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            if (env('ADMIN_EMAIL') && env('ADMIN_PASSWORD')) {
                User::updateOrCreate(['email' => env('ADMIN_EMAIL')], [
                    'name' => 'Admin RM Padang',
                    'password' => Hash::make(env('ADMIN_PASSWORD')),
                    'role' => 'admin',
                ]);
            }
        } else {
            User::updateOrCreate(['email' => 'admin@padang.local'], [
                'name' => 'Admin RM Padang',
                'password' => Hash::make('PadangAdmin123!'),
                'role' => 'admin',
            ]);

            User::updateOrCreate(['email' => 'staf@padang.local'], [
                'name' => 'Staf Rumah Makan',
                'password' => Hash::make('StafPadang123!'),
                'role' => 'staff',
            ]);
        }

        $dishes = [
            ['Rendang Sapi', 'Lauk utama', 'Daging sapi dimasak perlahan dengan rempah Minang.', 28000, 'porsi', '🥘', true],
            ['Ayam Pop', 'Lauk utama', 'Ayam lembut khas Minang dengan sambal merah.', 22000, 'potong', '🍗', true],
            ['Gulai Tunjang', 'Lauk utama', 'Gulai kaki sapi dengan kuah santan kaya rempah.', 26000, 'porsi', '🍛', false],
            ['Dendeng Balado', 'Lauk utama', 'Dendeng sapi renyah dengan lado merah.', 25000, 'porsi', '🥩', true],
            ['Telur Balado', 'Lauk pelengkap', 'Telur rebus dengan sambal balado.', 8000, 'butir', '🥚', false],
            ['Perkedel Kentang', 'Lauk pelengkap', 'Perkedel kentang gurih, digoreng hangat.', 6000, 'buah', '🥔', false],
            ['Daun Singkong', 'Sayur & sambal', 'Daun singkong rebus dengan sambal ijo.', 7000, 'porsi', '🥬', false],
            ['Samba Lado', 'Sayur & sambal', 'Sambal lado merah segar khas rumah makan.', 5000, 'porsi', '🌶️', false],
            ['Nasi Putih', 'Nasi & minuman', 'Nasi putih pulen hangat.', 7000, 'porsi', '🍚', false],
            ['Es Teh Manis', 'Nasi & minuman', 'Teh manis dingin yang menyegarkan.', 5000, 'gelas', '🧋', false],
        ];

        foreach ($dishes as [$name, $category, $description, $price, $unit, $emoji, $recommended]) {
            Dish::updateOrCreate(['name' => $name], [
                'category' => $category,
                'description' => $description,
                'price' => $price,
                'unit' => $unit,
                'emoji' => $emoji,
                'is_available' => true,
                'is_recommended' => $recommended,
                'minimum_stock' => 5,
            ]);
        }
    }
}
