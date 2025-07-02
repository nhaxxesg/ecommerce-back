<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener usuarios propietarios específicos
        $pizzaOwner = User::where('email', 'pizza@demo.com')->first();
        $burgerOwner = User::where('email', 'burger@demo.com')->first();
        $sushiOwner = User::where('email', 'sushi@demo.com')->first();

        // Pizza Palace
        Restaurant::create([
            'owner_id' => $pizzaOwner->id,
            'name' => 'Pizza Palace',
            'description' => 'Las mejores pizzas artesanales de la ciudad con ingredientes frescos importados de Italia',
            'cuisine_type' => 'Italiana',
            'address' => 'Av. Principal 123, San Miguel',
            'phone' => '987654321',
            'email' => 'contacto@pizzapalace.com',
            'opening_time' => '11:00',
            'closing_time' => '23:00',
            'image_url' => 'https://images.pexels.com/photos/1566837/pexels-photo-1566837.jpeg',
            'is_active' => true,
        ]);

        // Burger Express
        Restaurant::create([
            'owner_id' => $burgerOwner->id,
            'name' => 'Burger Express',
            'description' => 'Hamburguesas gourmet con ingredientes frescos y carne premium de la mejor calidad',
            'cuisine_type' => 'Americana',
            'address' => 'Calle Comercio 456, Miraflores',
            'phone' => '987654322',
            'email' => 'pedidos@burgerexpress.com',
            'opening_time' => '10:00',
            'closing_time' => '22:00',
            'image_url' => 'https://images.pexels.com/photos/1639557/pexels-photo-1639557.jpeg',
            'is_active' => true,
        ]);

        // Sushi Zen
        Restaurant::create([
            'owner_id' => $sushiOwner->id,
            'name' => 'Sushi Zen',
            'description' => 'Auténtica comida japonesa preparada por chefs expertos con pescado fresco de primera calidad',
            'cuisine_type' => 'Japonesa',
            'address' => 'Av. Libertad 789, San Isidro',
            'phone' => '987654323',
            'email' => 'reservas@sushizen.com',
            'opening_time' => '18:00',
            'closing_time' => '24:00',
            'image_url' => 'https://images.pexels.com/photos/248444/pexels-photo-248444.jpeg',
            'is_active' => true,
        ]);
    }
} 