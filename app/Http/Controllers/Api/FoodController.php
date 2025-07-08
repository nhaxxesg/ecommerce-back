<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\Restaurant;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
<<<<<<< HEAD
use Illuminate\Support\Facades\Config;
=======
>>>>>>> 2733e459c53be31b16d11e5ea89831f812f248a2

class FoodController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request, Restaurant $restaurant)
    {
        $query = $restaurant->foods();

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('available')) {
            $query->available();
        }

        $foods = $query->get();

        return response()->json($foods);
    }

    public function show(Food $food)
    {
        return response()->json($food->load('restaurant'));
    }

    public function store(Request $request, Restaurant $restaurant)
    {
        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'preparation_time' => 'nullable|integer|min:1',
        ]);

<<<<<<< HEAD
        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category' => $request->category,
            'preparation_time' => $request->preparation_time ?? 30,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('foods', 'public');
            $data['image_url'] = Config::get('app.url') . Storage::url($path);
        }

        $food = $restaurant->foods()->create($data);
=======
        try {
            // Procesar la imagen si se proporcionó una
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $this->imageService->uploadImage($request->file('image'), 'foods');
            }
>>>>>>> 2733e459c53be31b16d11e5ea89831f812f248a2

            $food = $restaurant->foods()->create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'category' => $request->category,
                'image_url' => $imagePath ? url(Storage::url($imagePath)) : null,
                'preparation_time' => $request->preparation_time ?? 30,
            ]);

            return response()->json($food->load('restaurant'), 201);
        } catch (\Exception $e) {
            // Si hubo un error y se subió una imagen, eliminarla
            if (isset($imagePath)) {
                $this->imageService->deleteImage($imagePath);
            }
            
            return response()->json([
                'message' => 'Error al crear el producto: ' . $e->getMessage()
            ], 422);
        }
    }

    public function update(Request $request, Food $food)
    {
        if ($food->restaurant->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_available' => 'sometimes|boolean',
            'preparation_time' => 'sometimes|integer|min:1',
        ]);

<<<<<<< HEAD
        $data = $request->only([
            'name', 'description', 'price', 'category', 
            'is_available', 'preparation_time'
        ]);

        if ($request->hasFile('image')) {
            if ($food->image_url) {
                $oldPath = str_replace('/storage/', '', parse_url($food->image_url, PHP_URL_PATH));
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('image')->store('foods', 'public');
            $data['image_url'] = Config::get('app.url') . Storage::url($path);
        }

        $food->update($data);
=======
        try {
            $updateData = $request->only([
                'name', 'description', 'price', 'category', 
                'is_available', 'preparation_time'
            ]);
>>>>>>> 2733e459c53be31b16d11e5ea89831f812f248a2

            // Procesar la nueva imagen si se proporcionó una
            if ($request->hasFile('image')) {
                // Eliminar la imagen anterior si existe
                if ($food->image_url) {
                    $oldPath = str_replace('/storage/', '', parse_url($food->image_url, PHP_URL_PATH));
                    $this->imageService->deleteImage($oldPath);
                }

                // Subir la nueva imagen
                $imagePath = $this->imageService->uploadImage($request->file('image'), 'foods');
                $updateData['image_url'] = url(Storage::url($imagePath));
            }

            $food->update($updateData);

            return response()->json($food->load('restaurant'));
        } catch (\Exception $e) {
            // Si hubo un error y se subió una imagen nueva, eliminarla
            if (isset($imagePath)) {
                $this->imageService->deleteImage($imagePath);
            }
            
            return response()->json([
                'message' => 'Error al actualizar el producto: ' . $e->getMessage()
            ], 422);
        }
    }

    public function destroy(Food $food, Request $request)
    {
        if ($food->restaurant->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Eliminar la imagen si existe
        if ($food->image_url) {
            $path = str_replace('/storage/', '', parse_url($food->image_url, PHP_URL_PATH));
            $this->imageService->deleteImage($path);
        }

        $food->delete();

        return response()->json(['message' => 'Food deleted successfully']);
    }

    public function toggleAvailability(Food $food, Request $request)
    {
        if ($food->restaurant->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $food->update(['is_available' => !$food->is_available]);

        return response()->json($food);
    }
} 