<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador Halcon',
            'username' => 'admin',
            'email' => 'admin@halcon.com',
            'password' => Hash::make('admin'),
            'role' => 'Admin',
            'active' => true,
        ]);

        User::create([
            'name' => 'Vendedor Estrella',
            'username' => 'ventas',
            'email' => 'ventas@halcon.com',
            'password' => Hash::make('password'),
            'role' => 'Sales',
            'active' => true,
        ]);

        Order::factory(50)->create();
    }
}
