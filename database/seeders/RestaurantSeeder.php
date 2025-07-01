<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $owners = User::where('role', 'owner')->get();

        $restaurants = [
            [
                'name' => 'Pizza Mario',
                'description' => 'Las mejores pizzas artesanales de Lima',
                'cuisine_type' => 'Italiana',
                'address' => 'Av. Larco 789, Miraflores, Lima',
                'phone' => '014567890',
                'email' => 'contacto@pizzamario.com',
                'opening_time' => '12:00',
                'closing_time' => '23:00',
                'image_url' => 'https://via.placeholder.com/400x300/FF6B6B/FFFFFF?text=Pizza+Mario',
            ],
            [
                'name' => 'Cevichería El Puerto',
                'description' => 'Ceviche fresco todos los días',
                'cuisine_type' => 'Peruana',
                'address' => 'Av. La Marina 456, San Miguel, Lima',
                'phone' => '014567891',
                'email' => 'info@elpuerto.com',
                'opening_time' => '11:00',
                'closing_time' => '22:00',
                'image_url' => 'https://via.placeholder.com/400x300/4ECDC4/FFFFFF?text=El+Puerto',
            ],
            [
                'name' => 'Burger House',
                'description' => 'Hamburguesas gourmet premium',
                'cuisine_type' => 'Americana',
                'address' => 'Av. Brasil 321, Breña, Lima',
                'phone' => '014567892',
                'email' => 'pedidos@burgerhouse.com',
                'opening_time' => '18:00',
                'closing_time' => '02:00',
                'image_url' => 'https://via.placeholder.com/400x300/45B7D1/FFFFFF?text=Burger+House',
            ],
            [
                'name' => 'Sushi Zen',
                'description' => 'Auténtica comida japonesa',
                'cuisine_type' => 'Japonesa',
                'address' => 'Av. Conquistadores 654, San Isidro, Lima',
                'phone' => '014567893',
                'email' => 'zen@sushi.com',
                'opening_time' => '19:00',
                'closing_time' => '24:00',
                'image_url' => 'https://via.placeholder.com/400x300/F7DC6F/000000?text=Sushi+Zen',
            ],
        ];

        foreach ($restaurants as $index => $restaurantData) {
            Restaurant::create(array_merge($restaurantData, [
                'owner_id' => $owners[$index % $owners->count()]->id,
            ]));
        }
    }
} 