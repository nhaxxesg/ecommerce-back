<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\Restaurant;
use Illuminate\Http\Request;

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
            'image_url' => 'nullable|url',
            'preparation_time' => 'nullable|integer|min:1',
        ]);

        $food = $restaurant->foods()->create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category' => $request->category,
            'image_url' => $request->image_url,
            'preparation_time' => $request->preparation_time ?? 30,
        ]);

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
            'image_url' => 'nullable|url',
            'is_available' => 'sometimes|boolean',
            'preparation_time' => 'sometimes|integer|min:1',
        ]);

        $food->update($request->only([
            'name', 'description', 'price', 'category', 
            'image_url', 'is_available', 'preparation_time'
        ]));

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