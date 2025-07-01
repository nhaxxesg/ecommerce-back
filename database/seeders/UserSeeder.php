<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create test client
        User::create([
            'name' => 'Cliente Test',
            'email' => 'cliente@test.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'phone' => '987654321',
            'address' => 'Av. Lima 123, Lima, Perú',
        ]);

        // Create test owner
        User::create([
            'name' => 'Dueño Test',
            'email' => 'dueno@test.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'phone' => '987654322',
            'address' => 'Av. Arequipa 456, Lima, Perú',
        ]);

        // Create additional clients
        User::factory(5)->create(['role' => 'client']);
        
        // Create additional owners
        User::factory(3)->create(['role' => 'owner']);
    }
} 