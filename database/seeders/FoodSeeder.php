<?php

namespace Database\Seeders;

use App\Models\Food;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = Restaurant::all();

        $foodsByRestaurant = [
            'Pizza Mario' => [
                ['name' => 'Pizza Margherita', 'description' => 'Tomate, mozzarella y albahaca', 'price' => 25.00, 'category' => 'Pizza'],
                ['name' => 'Pizza Pepperoni', 'description' => 'Pepperoni y mozzarella', 'price' => 28.00, 'category' => 'Pizza'],
                ['name' => 'Pizza Hawaiana', 'description' => 'Jamón, piña y mozzarella', 'price' => 30.00, 'category' => 'Pizza'],
                ['name' => 'Lasagna', 'description' => 'Lasagna tradicional italiana', 'price' => 22.00, 'category' => 'Pasta'],
                ['name' => 'Tiramisu', 'description' => 'Postre italiano clásico', 'price' => 12.00, 'category' => 'Postre'],
            ],
            'Cevichería El Puerto' => [
                ['name' => 'Ceviche Clásico', 'description' => 'Pescado fresco con limón y ají', 'price' => 18.00, 'category' => 'Ceviche'],
                ['name' => 'Ceviche Mixto', 'description' => 'Pescado y mariscos frescos', 'price' => 22.00, 'category' => 'Ceviche'],
                ['name' => 'Tiradito', 'description' => 'Pescado en finas láminas', 'price' => 20.00, 'category' => 'Tiradito'],
                ['name' => 'Arroz con Mariscos', 'description' => 'Arroz amarillo con mariscos', 'price' => 24.00, 'category' => 'Arroz'],
                ['name' => 'Leche de Tigre', 'description' => 'El líquido del ceviche', 'price' => 8.00, 'category' => 'Bebida'],
            ],
            'Burger House' => [
                ['name' => 'Classic Burger', 'description' => 'Carne, lechuga, tomate, cebolla', 'price' => 15.00, 'category' => 'Hamburguesa'],
                ['name' => 'Cheese Burger', 'description' => 'Con queso cheddar extra', 'price' => 17.00, 'category' => 'Hamburguesa'],
                ['name' => 'BBQ Burger', 'description' => 'Con salsa BBQ y cebolla caramelizada', 'price' => 19.00, 'category' => 'Hamburguesa'],
                ['name' => 'Papas Fritas', 'description' => 'Papas crujientes caseras', 'price' => 8.00, 'category' => 'Acompañamiento'],
                ['name' => 'Milkshake Vainilla', 'description' => 'Batido cremoso de vainilla', 'price' => 10.00, 'category' => 'Bebida'],
            ],
            'Sushi Zen' => [
                ['name' => 'Sushi Sake', 'description' => 'Rollitos de salmón fresco', 'price' => 16.00, 'category' => 'Sushi'],
                ['name' => 'Sushi Atún', 'description' => 'Rollitos de atún rojo', 'price' => 18.00, 'category' => 'Sushi'],
                ['name' => 'California Roll', 'description' => 'Cangrejo, palta y pepino', 'price' => 14.00, 'category' => 'Maki'],
                ['name' => 'Ramen Tonkotsu', 'description' => 'Sopa de fideos con cerdo', 'price' => 20.00, 'category' => 'Ramen'],
                ['name' => 'Mochi', 'description' => 'Postre japonés de arroz', 'price' => 6.00, 'category' => 'Postre'],
            ],
        ];

        foreach ($restaurants as $restaurant) {
            $foods = $foodsByRestaurant[$restaurant->name] ?? [];
            
            foreach ($foods as $foodData) {
                Food::create(array_merge($foodData, [
                    'restaurant_id' => $restaurant->id,
                    'image_url' => "https://via.placeholder.com/300x200/E74C3C/FFFFFF?text=" . urlencode($foodData['name']),
                    'preparation_time' => rand(15, 45),
                ]));
            }
        }
    }
} 