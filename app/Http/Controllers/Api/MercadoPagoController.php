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

    public function getConfig()
    {
        return response()->json([
            'public_key' => config('services.mercadopago.public_key'),
            'mode' => config('services.mercadopago.mode'),
        ]);
    }

    public function createPreference(Request $request)
    {
        try {
            Log::error('Iniciando creación de preferencia MercadoPago', [
                'request_data' => $request->all(),
                'user_id' => auth()->id(),
                'timestamp' => now()->toDateTimeString()
            ]);

            $items = $request->validate([
                'items' => 'required|array',
                'items.*.title' => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.currency_id' => 'required|string',
            ])['items'];

            Log::error('Datos validados correctamente', [
                'validated_items' => $items
            ]);

            try {
                $init_point = $this->mercadoPagoService->createPreference($items);
                
                Log::error('Preferencia creada exitosamente', [
                    'init_point' => $init_point
                ]);
                
                return response()->json(['init_point' => $init_point]);
            } catch (\MercadoPago\Exceptions\MPApiException $e) {
                Log::error('Error específico de MercadoPago', [
                    'error_message' => $e->getMessage(),
                    'api_response' => $e->getApiResponse() ? json_encode($e->getApiResponse()->getContent()) : null,
                    'status_code' => $e->getApiResponse() ? $e->getApiResponse()->getStatusCode() : null
                ]);
                return response()->json([
                    'error' => 'Error de MercadoPago: ' . $e->getMessage(),
                    'details' => $e->getApiResponse() ? $e->getApiResponse()->getContent() : null
                ], 500);
            } catch (\Exception $e) {
                Log::error('Error en MercadoPagoService', [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'stack_trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error general en createPreference', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Error creating MercadoPago preference: ' . $e->getMessage(),
                'details' => 'Check logs for more information'
            ], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        Log::info('MercadoPago webhook received', $request->all());

        try {
            $type = $request->input('type');
            $id = $request->input('data.id');

            if ($type === 'payment') {
                // Aquí implementarás la lógica para actualizar el estado del pedido
                // cuando recibas la confirmación de pago
                return response()->json(['status' => 'payment processed']);
            }

            return response()->json(['status' => 'webhook received']);
        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 