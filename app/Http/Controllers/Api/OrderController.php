<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Food;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }

    public function index(Request $request)
    {
        $query = $request->user()->orders()->with(['restaurant', 'items.food']);

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($orders);
    }

    public function show(Order $order, Request $request)
    {
        if ($order->user_id !== $request->user()->id && 
            $order->restaurant->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order->load(['restaurant', 'items.food', 'user']));
    }

    public function store(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'items' => 'required|array|min:1',
            'items.*.food_id' => 'required|exists:foods,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.special_instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Verify all foods belong to the same restaurant
            $foodIds = collect($request->items)->pluck('food_id');
            $foods = Food::whereIn('id', $foodIds)->with('restaurant')->get();

            if ($foods->pluck('restaurant_id')->unique()->count() > 1) {
                return response()->json(['message' => 'All items must be from the same restaurant'], 400);
            }

            if ($foods->first()->restaurant_id != $request->restaurant_id) {
                return response()->json(['message' => 'Items do not belong to the specified restaurant'], 400);
            }

            // Create order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'restaurant_id' => $request->restaurant_id,
                'subtotal' => 0,
                'total' => 0,
                'notes' => $request->notes,
                'customer_details' => [
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'phone' => $request->user()->phone,
                    'address' => $request->user()->address,
                ]
            ]);

            $subtotal = 0;

            // Create order items
            foreach ($request->items as $item) {
                $food = $foods->find($item['food_id']);
                
                if (!$food->is_available) {
                    throw new \Exception("Food {$food->name} is not available");
                }

                $itemTotal = $food->price * $item['quantity'];
                $subtotal += $itemTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'food_id' => $food->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $food->price,
                    'total_price' => $itemTotal,
                    'special_instructions' => $item['special_instructions'] ?? null,
                ]);
            }

            // Update order totals
            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal, // No taxes or fees for now
            ]);

            DB::commit();

            return response()->json($order->load(['restaurant', 'items.food']), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function createPayment(Order $order, Request $request)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Order already paid'], 400);
        }

        try {
            $preference = $this->mercadoPagoService->createPreference($order);
            
            $order->update([
                'mercadopago_preference_id' => $preference->id
            ]);

            return response()->json([
                'preference_id' => $preference->id,
                'public_key' => config('mercadopago.public_key'),
                'init_point' => $preference->init_point,
                'sandbox_init_point' => $preference->sandbox_init_point,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment creation failed: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Order $order, Request $request)
    {
        if ($order->restaurant->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:confirmed,preparing,ready,completed,cancelled'
        ]);

        $order->update(['status' => $request->status]);

        return response()->json($order);
    }

    public function restaurantOrders(Request $request)
    {
        $restaurantIds = $request->user()->restaurants()->pluck('id');
        
        $query = Order::whereIn('restaurant_id', $restaurantIds)
                     ->with(['user', 'items.food', 'restaurant']);

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($orders);
    }
} 