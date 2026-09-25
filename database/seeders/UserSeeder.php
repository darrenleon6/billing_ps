<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Akun Admin / Pemilik
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Pemilik Rental (Admin)',
                'username' => 'admin',
                'role' => 'admin',
                'password' => Hash::make('admin123'), // Ganti dengan password yang lebih kuat saat produksi
            ]
        );

        // 2. Akun Operator / Kasir Shift 1
        User::updateOrCreate(
            ['username' => 'kasir1'],
            [
                'name' => 'Operator Shift Pagi',
                'username' => 'kasir1',
                'role' => 'operator',
                'password' => Hash::make('kasir123'),
            ]
        );

        // 3. Akun Operator / Kasir Shift 2
        User::updateOrCreate(
            ['username' => 'kasir2'],
            [
                'name' => 'Operator Shift Malam',
                'username' => 'kasir2',
                'role' => 'operator',
                'password' => Hash::make('kasir123'),
            ]
        );
    }
}