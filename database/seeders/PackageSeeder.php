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
            'price' => 8000,
        ]);

        Package::create([
            'name' => 'Paket 2 Jam',
            'duration_minutes' => 120,
            'price' => 16000,
        ]);

        Package::create([
            'name' => 'Paket 3 Jam',
            'duration_minutes' => 180,
            'price' => 24000,
        ]);

        Package::create([
            'name' => 'Paket 4 Jam',
            'duration_minutes' => 240,
            'price' => 32000,
        ]);

    }
}