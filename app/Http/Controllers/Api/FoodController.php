<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class FoodController extends Controller
{
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

        return response()->json($food->load('restaurant'), 201);
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

        return response()->json($food->load('restaurant'));
    }

    public function destroy(Food $food, Request $request)
    {
        if ($food->restaurant->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
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