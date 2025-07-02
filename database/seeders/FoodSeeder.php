<?php

namespace Database\Seeders;

use App\Models\Food;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener restaurantes específicos
        $pizzaPalace = Restaurant::where('name', 'Pizza Palace')->first();
        $burgerExpress = Restaurant::where('name', 'Burger Express')->first();
        $sushiZen = Restaurant::where('name', 'Sushi Zen')->first();

        // Comidas para Pizza Palace
        if ($pizzaPalace) {
            $pizzaFoods = [
                [
                    'name' => 'Pizza Margherita',
                    'description' => 'Clásica pizza con tomate San Marzano, mozzarella di bufala y albahaca fresca',
                    'price' => 18.90,
                    'category' => 'Pizzas',
                    'image_url' => 'https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg',
                    'preparation_time' => 25,
                ],
                [
                    'name' => 'Pizza Pepperoni',
                    'description' => 'Pizza con salsa de tomate, mozzarella y pepperoni premium',
                    'price' => 22.90,
                    'category' => 'Pizzas',
                    'image_url' => 'https://images.pexels.com/photos/2147491/pexels-photo-2147491.jpeg',
                    'preparation_time' => 25,
                ],
                [
                    'name' => 'Pizza Cuatro Quesos',
                    'description' => 'Mozzarella, gorgonzola, parmesano y queso de cabra',
                    'price' => 26.90,
                    'category' => 'Pizzas',
                    'image_url' => 'https://images.pexels.com/photos/4109111/pexels-photo-4109111.jpeg',
                    'preparation_time' => 30,
                ],
                [
                    'name' => 'Lasagna Bolognesa',
                    'description' => 'Tradicional lasagna con salsa bolognesa y bechamel casera',
                    'price' => 24.90,
                    'category' => 'Pastas',
                    'image_url' => 'https://images.pexels.com/photos/4079520/pexels-photo-4079520.jpeg',
                    'preparation_time' => 35,
                ],
                [
                    'name' => 'Tiramisu',
                    'description' => 'Postre italiano tradicional con café y mascarpone',
                    'price' => 12.90,
                    'category' => 'Postres',
                    'image_url' => 'https://images.pexels.com/photos/6880219/pexels-photo-6880219.jpeg',
                    'preparation_time' => 10,
                ],
            ];

            foreach ($pizzaFoods as $foodData) {
                Food::create(array_merge($foodData, [
                    'restaurant_id' => $pizzaPalace->id,
                    'is_available' => true,
                ]));
            }
        }

        // Comidas para Burger Express
        if ($burgerExpress) {
            $burgerFoods = [
                [
                    'name' => 'Burger Clásica',
                    'description' => 'Hamburguesa con carne angus, lechuga, tomate, cebolla y papas fritas',
                    'price' => 16.50,
                    'category' => 'Hamburguesas',
                    'image_url' => 'https://images.pexels.com/photos/1633578/pexels-photo-1633578.jpeg',
                    'preparation_time' => 20,
                ],
                [
                    'name' => 'Burger BBQ',
                    'description' => 'Hamburguesa con carne, bacon, queso cheddar, salsa BBQ y aros de cebolla',
                    'price' => 19.90,
                    'category' => 'Hamburguesas',
                    'image_url' => 'https://images.pexels.com/photos/1552641/pexels-photo-1552641.jpeg',
                    'preparation_time' => 22,
                ],
                [
                    'name' => 'Burger Doble',
                    'description' => 'Doble carne, doble queso, bacon y vegetales frescos',
                    'price' => 24.90,
                    'category' => 'Hamburguesas',
                    'image_url' => 'https://images.pexels.com/photos/3915906/pexels-photo-3915906.jpeg',
                    'preparation_time' => 25,
                ],
                [
                    'name' => 'Papas Premium',
                    'description' => 'Papas rústicas con hierbas y parmesano',
                    'price' => 8.90,
                    'category' => 'Acompañamientos',
                    'image_url' => 'https://images.pexels.com/photos/1583884/pexels-photo-1583884.jpeg',
                    'preparation_time' => 15,
                ],
                [
                    'name' => 'Milkshake Oreo',
                    'description' => 'Batido cremoso con galletas Oreo y crema chantilly',
                    'price' => 12.90,
                    'category' => 'Bebidas',
                    'image_url' => 'https://images.pexels.com/photos/3738073/pexels-photo-3738073.jpeg',
                    'preparation_time' => 8,
                ],
            ];

            foreach ($burgerFoods as $foodData) {
                Food::create(array_merge($foodData, [
                    'restaurant_id' => $burgerExpress->id,
                    'is_available' => true,
                ]));
            }
        }

        // Comidas para Sushi Zen
        if ($sushiZen) {
            $sushiFoods = [
                [
                    'name' => 'Sushi Salmón',
                    'description' => 'Nigiri de salmón fresco sobre arroz sushi perfectamente sazonado',
                    'price' => 16.90,
                    'category' => 'Sushi',
                    'image_url' => 'https://images.pexels.com/photos/357756/pexels-photo-357756.jpeg',
                    'preparation_time' => 15,
                ],
                [
                    'name' => 'California Roll',
                    'description' => 'Rollo con cangrejo, palta, pepino y sésamo por fuera',
                    'price' => 14.90,
                    'category' => 'Makis',
                    'image_url' => 'https://images.pexels.com/photos/2098085/pexels-photo-2098085.jpeg',
                    'preparation_time' => 12,
                ],
                [
                    'name' => 'Philadelphia Roll',
                    'description' => 'Rollo con salmón, queso philadelphia y pepino',
                    'price' => 18.90,
                    'category' => 'Makis',
                    'image_url' => 'https://images.pexels.com/photos/248444/pexels-photo-248444.jpeg',
                    'preparation_time' => 15,
                ],
                [
                    'name' => 'Ramen Tonkotsu',
                    'description' => 'Sopa tradicional con fideos ramen, chashu y huevo',
                    'price' => 22.90,
                    'category' => 'Ramen',
                    'image_url' => 'https://images.pexels.com/photos/884596/pexels-photo-884596.jpeg',
                    'preparation_time' => 20,
                ],
                [
                    'name' => 'Mochi Helado',
                    'description' => 'Postre japonés de mochi relleno con helado de vainilla',
                    'price' => 9.90,
                    'category' => 'Postres',
                    'image_url' => 'https://images.pexels.com/photos/4686819/pexels-photo-4686819.jpeg',
                    'preparation_time' => 5,
                ],
            ];

            foreach ($sushiFoods as $foodData) {
                Food::create(array_merge($foodData, [
                    'restaurant_id' => $sushiZen->id,
                    'is_available' => true,
                ]));
            }
        }
    }
} 