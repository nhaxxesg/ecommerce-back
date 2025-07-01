<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    protected $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }

    public function webhook(Request $request)
    {
        try {
            Log::info('MercadoPago Webhook received', $request->all());

            $data = $request->all();
            
            if (isset($data['type']) && $data['type'] === 'payment') {
                $order = $this->mercadoPagoService->processWebhook($data);
                
                if ($order) {
                    Log::info('Order payment status updated', [
                        'order_id' => $order->id,
                        'payment_status' => $order->payment_status,
                        'status' => $order->status,
                        'payment_id' => $order->payment_id
                    ]);
                }
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('MercadoPago Webhook error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    public function getConfig()
    {
        return response()->json([
            'public_key' => config('mercadopago.public_key'),
            'sandbox' => config('mercadopago.sandbox'),
        ]);
    }
} 