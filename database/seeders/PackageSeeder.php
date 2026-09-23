<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data lama jika ada (opsional)
        Package::truncate();

        Package::create([
            'name' => 'Paket 1 Jam',
            'duration_minutes' => 60,
            'price' => 12000,
        ]);

        Package::create([
            'name' => 'Paket 2 Jam',
            'duration_minutes' => 120,
            'price' => 22000,
        ]);

        Package::create([
            'name' => 'Paket 3 Jam',
            'duration_minutes' => 180,
            'price' => 30000,
        ]);

        Package::create([
            'name' => 'Paket Begadang (5 Jam)',
            'duration_minutes' => 300,
            'price' => 45000,
        ]);
    }
}