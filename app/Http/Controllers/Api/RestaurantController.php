<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Services\RucValidationService;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class RestaurantController extends Controller
{
    protected $rucValidationService;
    protected $imageService;

    public function __construct(RucValidationService $rucValidationService, ImageService $imageService)
    {
        $this->rucValidationService = $rucValidationService;
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        $query = Restaurant::with(['owner', 'foods' => function($q) {
            $q->available();
        }])->active();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cuisine_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('cuisine_type')) {
            $query->where('cuisine_type', $request->cuisine_type);
        }

        $restaurants = $query->paginate(15);

        return response()->json($restaurants);
    }

    public function show($id)
    {
        $restaurant = Restaurant::with(['owner', 'foods' => function($q) {
            $q->available();
        }])->findOrFail($id);

        return response()->json($restaurant);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Restaurant::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cuisine_type' => 'nullable|string|max:100',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'ruc' => 'required|string|size:11|unique:restaurants',
        ]);

        try {
            // Procesar la imagen si se proporcionó una
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $this->imageService->uploadImage($request->file('image'));
            }

            $restaurant = Restaurant::create([
                'owner_id' => $request->user()->id,
                'name' => $request->name,
                'ruc' => $request->ruc,
                'description' => $request->description,
                'cuisine_type' => $request->cuisine_type,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'opening_time' => $request->opening_time,
                'closing_time' => $request->closing_time,
                'image_url' => $imagePath ? Storage::url($imagePath) : null,
            ]);

            Log::info('Restaurante creado', [
                'restaurant' => $restaurant
            ]);

            return response()->json($restaurant->load('owner'), 201);
        } catch (\Exception $e) {
            // Si hubo un error y se subió una imagen, eliminarla
            if (isset($imagePath)) {
                $this->imageService->deleteImage($imagePath);
            }
            
            return response()->json([
                'message' => 'Error al validar el RUC: ' . $e->getMessage()
            ], 422);
        }
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $this->authorize('update', $restaurant);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'cuisine_type' => 'nullable|string|max:100',
            'address' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'nullable|email|max:255',
            'opening_time' => 'sometimes|required|date_format:H:i',
            'closing_time' => 'sometimes|required|date_format:H:i|after:opening_time',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'sometimes|boolean',
            'ruc' => 'sometimes|required|string|size:11|unique:restaurants,ruc,' . $restaurant->id,
        ]);

        try {
            $updateData = $request->only([
                'name', 'description', 'cuisine_type', 'address', 'phone', 
                'email', 'opening_time', 'closing_time', 'is_active'
            ]);

            // Si se está actualizando el RUC, validarlo
            if ($request->has('ruc') && $request->ruc !== $restaurant->ruc) {
                $rucData = $this->rucValidationService->validateRuc($request->ruc);
                $updateData['ruc'] = $request->ruc;
                $updateData['razon_social'] = $rucData['razonSocial'];
            }

            // Procesar la nueva imagen si se proporcionó una
            if ($request->hasFile('image')) {
                // Eliminar la imagen anterior si existe
                if ($restaurant->image_url) {
                    $oldPath = str_replace('/storage/', '', parse_url($restaurant->image_url, PHP_URL_PATH));
                    $this->imageService->deleteImage($oldPath);
                }

                // Subir la nueva imagen
                $imagePath = $this->imageService->uploadImage($request->file('image'));
                $updateData['image_url'] = Storage::url($imagePath);
            }

            $restaurant->update($updateData);

            return response()->json($restaurant->load('owner'));
        } catch (\Exception $e) {
            // Si hubo un error y se subió una imagen nueva, eliminarla
            if (isset($imagePath)) {
                $this->imageService->deleteImage($imagePath);
            }
            
            return response()->json([
                'message' => 'Error al actualizar el restaurante: ' . $e->getMessage()
            ], 422);
        }
    }

    public function destroy(Restaurant $restaurant)
    {
        $this->authorize('delete', $restaurant);
        
        $restaurant->delete();

        return response()->json(['message' => 'Restaurant deleted successfully']);
    }

    public function myRestaurants(Request $request)
    {
        $restaurants = $request->user()->restaurants()->with('foods')->get();
        
        return response()->json($restaurants);
    }
} 