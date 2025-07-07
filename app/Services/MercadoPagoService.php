<?php

namespace App\Services;

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    public function __construct()
    {
        $accessToken = config('services.mercadopago.access_token');
        $publicKey = config('services.mercadopago.public_key');
        $mode = config('services.mercadopago.mode');

        Log::error('Configuración de MercadoPago', [
            'access_token_length' => $accessToken ? strlen($accessToken) : 0,
            'public_key_length' => $publicKey ? strlen($publicKey) : 0,
            'mode' => $mode,
            'frontend_url' => config('services.frontend_url'),
            'app_url' => config('app.url')
        ]);
        
        if (!$accessToken) {
            Log::error('MercadoPago access token no configurado');
            throw new \Exception('MercadoPago access token not configured');
        }

        if (!$publicKey) {
            Log::error('MercadoPago public key no configurado');
            throw new \Exception('MercadoPago public key not configured');
        }

        try {
            MercadoPagoConfig::setAccessToken($accessToken);
            Log::error('MercadoPago configurado correctamente');
        } catch (\Exception $e) {
            Log::error('Error configurando MercadoPago', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Error configuring MercadoPago: ' . $e->getMessage());
        }
    }

    public function createPreference(array $items)
    {
        try {
            Log::error('Iniciando creación de preferencia en MercadoPagoService', [
                'items' => $items,
                'timestamp' => now()->toDateTimeString()
            ]);

            if (empty($items)) {
                throw new \Exception('No items provided for preference creation');
            }

            $client = new PreferenceClient();
            
            // Validar que cada item tenga los campos requeridos y los valores sean válidos
            foreach ($items as $index => $item) {
                Log::error("Validando item {$index}", [
                    'item_data' => $item
                ]);
                
                if (!isset($item['title'], $item['quantity'], $item['unit_price'], $item['currency_id'])) {
                    Log::error("Item {$index} inválido", [
                        'item_data' => $item,
                        'missing_fields' => array_diff(
                            ['title', 'quantity', 'unit_price', 'currency_id'],
                            array_keys($item)
                        )
                    ]);
                    throw new \Exception('Invalid item format. Required fields: title, quantity, unit_price, currency_id');
                }

                // Validar tipos de datos
                if (!is_string($item['title'])) {
                    throw new \Exception("Item {$index}: title must be a string");
                }
                if (!is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                    throw new \Exception("Item {$index}: quantity must be a positive number");
                }
                if (!is_numeric($item['unit_price']) || $item['unit_price'] <= 0) {
                    throw new \Exception("Item {$index}: unit_price must be a positive number");
                }
                if (!is_string($item['currency_id']) || $item['currency_id'] !== 'PEN') {
                    throw new \Exception("Item {$index}: currency_id must be 'PEN'");
                }

                // Convertir explícitamente los valores numéricos
                $items[$index]['quantity'] = (int)$item['quantity'];
                $items[$index]['unit_price'] = (float)$item['unit_price'];
            }

            // Obtener la URL del frontend con fallback a localhost:5173
            $frontendUrl = config('services.frontend_url', 'http://localhost:5173');
            
            // Asegurarnos de que las URLs no terminen en /
            $frontendUrl = rtrim($frontendUrl, '/');

            // Construir la URL del webhook manualmente
            $webhookUrl = config('app.url') . '/api/mercadopago/webhook';

            Log::error('Configurando URLs de preferencia', [
                'frontend_url' => $frontendUrl,
                'webhook_url' => $webhookUrl,
                'success_url' => $frontendUrl . '/payment/success',
                'failure_url' => $frontendUrl . '/payment/failure',
                'pending_url' => $frontendUrl . '/payment/pending'
            ]);

            $preferenceData = [
                "items" => $items,
                "back_urls" => [
                    'success' => $frontendUrl . '/payment/success',
                    'failure' => $frontendUrl . '/payment/failure',
                    'pending' => $frontendUrl . '/payment/pending'
                ],
                "notification_url" => $webhookUrl,
                "statement_descriptor" => "Comida Express",
                "external_reference" => uniqid("CE-"), // CE = Comida Express
                "expires" => false,
                "auto_return" => "approved"
            ];

            Log::error('Enviando datos de preferencia a MercadoPago', [
                'preference_data' => $preferenceData
            ]);

            try {
                $preference = $client->create($preferenceData);
                Log::error('Respuesta de MercadoPago', [
                    'raw_response' => json_encode($preference)
                ]);
            } catch (\MercadoPago\Exceptions\MPApiException $e) {
                Log::error('Error de API de MercadoPago', [
                    'message' => $e->getMessage(),
                    'status' => $e->getApiResponse()->getStatusCode(),
                    'response' => json_encode($e->getApiResponse()->getContent())
                ]);
                throw $e;
            }

            if (!isset($preference->init_point)) {
                Log::error('Respuesta inválida de MercadoPago', [
                    'preference' => json_encode($preference)
                ]);
                throw new \Exception('Invalid preference response from MercadoPago');
            }

            Log::error('Preferencia creada exitosamente', [
                'init_point' => $preference->init_point
            ]);
            
            return $preference->init_point;

        } catch (MPApiException $e) {
            Log::error('MercadoPago API Error', [
                'items' => $items,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Error creating MercadoPago preference: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error creating preference', [
                'items' => $items,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getPayment($paymentId)
    {
        $client = new PaymentClient();
        return $client->get($paymentId);
    }

    public function processWebhook($data)
    {
        if ($data['type'] === 'payment') {
            $payment = $this->getPayment($data['data']['id']);
            
            $order = Order::where('order_number', $payment->external_reference)->first();
            
            if ($order) {
                $order->payment_id = $payment->id;
                
                switch ($payment->status) {
                    case 'approved':
                        $order->payment_status = 'paid';
                        $order->status = 'confirmed';
                        break;
                    case 'rejected':
                    case 'cancelled':
                        $order->payment_status = 'failed';
                        $order->status = 'cancelled';
                        break;
                    case 'pending':
                    case 'in_process':
                        $order->payment_status = 'pending';
                        break;
                }
                
                $order->save();
                return $order;
            }
        }
        
        return null;
    }

    protected function getNotificationUrl(): string
    {
        return route('mercadopago.webhook');
    }
} 