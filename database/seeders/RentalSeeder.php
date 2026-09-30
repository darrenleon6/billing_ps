<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Console;
use App\Models\Product;

class RentalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Unit PS
        Console::create([
            'name' => 'PS4 - Unit 01',
            'type' => 'PS4',
            'hourly_rate' => 8000,
            'status' => 'ready',
        ]);

        Console::create([
            'name' => 'PS4 - Unit 02',
            'type' => 'PS4',
            'hourly_rate' => 8000,
            'status' => 'ready',
        ]);

        Console::create([
            'name' => 'PS4 - Unit 03',
            'type' => 'PS4',
            'hourly_rate' => 8000,
            'status' => 'ready',
        ]);
        Console::create([
            'name' => 'PS4 - Unit 04',
            'type' => 'PS4',
            'hourly_rate' => 8000,
            'status' => 'ready',
        ]);
        Console::create([
            'name' => 'PS4 - Unit 05',
            'type' => 'PS4',
            'hourly_rate' => 8000,
            'status' => 'ready',
        ]);
        Console::create([
            'name' => 'PS4 - Unit 06',
            'type' => 'PS4',
            'hourly_rate' => 8000,
            'status' => 'ready',
        ]);
        Console::create([
            'name' => 'PS4 - Unit 07',
            'type' => 'PS4',
            'hourly_rate' => 8000,
            'status' => 'ready',
        ]);
        Console::create([
            'name' => 'PS4 - Unit 08',
            'type' => 'PS4',
            'hourly_rate' => 8000,
            'status' => 'ready',
        ]);
        Console::create([
            'name' => 'PS4 - Unit 09',
            'type' => 'PS4',
            'hourly_rate' => 8000,
            'status' => 'ready',
        ]);
        Console::create([
            'name' => 'PS4 - Unit 10',
            'type' => 'PS4',
            'hourly_rate' => 8000,
            'status' => 'ready',
        ]);
        Console::create([
            'name' => 'PS4 - Unit 11',
            'type' => 'PS4',
            'hourly_rate' => 8000,
            'status' => 'ready',
        ]);

        // 2. Data Produk FnB
        Product::create([
            'name' => 'Teh Gelas',
            'category' => 'Minuman',
            'price' => 1500,
            'stock' => 50,
        ]);

        Product::create([
            'name' => 'Jari-Jari',
            'category' => 'Snack',
            'price' => 1500,
            'stock' => 30,
        ]);

        Product::create([
            'name' => 'Pop Mie',
            'category' => 'Makanan',
            'price' => 10000,
            'stock' => 20,
        ]);
    }
}