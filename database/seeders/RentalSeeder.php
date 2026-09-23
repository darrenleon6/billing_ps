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