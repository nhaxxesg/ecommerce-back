<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear un usuario cliente de ejemplo
        User::create([
            'name' => 'Juan Pérez',
            'email' => 'cliente@demo.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'phone' => '987654321',
            'address' => 'Av. Principal 123, Lima',
        ]);

        // Crear usuarios propietarios de restaurantes
        User::create([
            'name' => 'María García',
            'email' => 'pizza@demo.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'phone' => '987654322',
            'address' => 'Av. Libertad 456, Lima',
        ]);

        User::create([
            'name' => 'Carlos Rodríguez',
            'email' => 'burger@demo.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'phone' => '987654323',
            'address' => 'Calle Comercio 789, Lima',
        ]);

        User::create([
            'name' => 'Ana Sánchez',
            'email' => 'sushi@demo.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'phone' => '987654324',
            'address' => 'Av. Central 101, Lima',
        ]);

        // Crear más clientes de ejemplo
        User::create([
            'name' => 'Pedro López',
            'email' => 'pedro@demo.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'phone' => '987654325',
            'address' => 'Jr. Los Olivos 202, Lima',
        ]);
    }
} 