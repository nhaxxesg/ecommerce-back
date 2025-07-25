<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\FoodController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\MercadoPagoController;
use App\Http\Controllers\Api\MercadoPagoWebhookController;
use App\Http\Controllers\Api\ComplaintBookController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// MercadoPago Routes (public)
Route::get('/mercadopago/config', [MercadoPagoController::class, 'getConfig']);
Route::post('/mercadopago/create-preference', [MercadoPagoController::class, 'createPreference']);
Route::post('/mercadopago/webhook', [MercadoPagoWebhookController::class, 'handle'])
    ->name('mercadopago.webhook')
    ->withoutMiddleware(['auth:sanctum']);

// Public restaurant and food routes
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show']);
Route::get('/restaurants/{restaurant}/foods', [FoodController::class, 'index']);
Route::get('/foods/{food}', [FoodController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Complaint Book Route (protected)
    Route::post('/complaint-book', [ComplaintBookController::class, 'store']);

    // Restaurant management (owners only)
    Route::post('/restaurants', [RestaurantController::class, 'store']);
    Route::put('/restaurants/{restaurant}', [RestaurantController::class, 'update']);
    Route::delete('/restaurants/{restaurant}', [RestaurantController::class, 'destroy']);
    Route::get('/my-restaurants', [RestaurantController::class, 'myRestaurants']);

    // Food management (owners only)
    Route::post('/restaurants/{restaurant}/foods', [FoodController::class, 'store']);
    Route::put('/foods/{food}', [FoodController::class, 'update']);
    Route::delete('/foods/{food}', [FoodController::class, 'destroy']);
    Route::patch('/foods/{food}/toggle-availability', [FoodController::class, 'toggleAvailability']);

    // Order management
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/payment', [OrderController::class, 'createPayment']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    
    // Restaurant orders (for owners)
    Route::get('/restaurant-orders', [OrderController::class, 'restaurantOrders']);
}); 