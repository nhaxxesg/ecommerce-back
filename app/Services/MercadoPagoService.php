<?php

namespace App\Services;

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use App\Models\Order;

class MercadoPagoService
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));
    }

    public function createPreference(Order $order)
    {
        $client = new PreferenceClient();

        $items = $order->items->map(function ($item) {
            return [
                "id" => $item->food->id,
                "title" => $item->food->name,
                "description" => $item->food->description,
                "quantity" => $item->quantity,
                "unit_price" => (float) $item->unit_price,
            ];
        })->toArray();

        $preference = $client->create([
            "items" => $items,
            "payer" => [
                "name" => $order->user->name,
                "email" => $order->user->email,
                "phone" => [
                    "area_code" => "51",
                    "number" => $order->user->phone ?? "999999999"
                ]
            ],
            "external_reference" => $order->order_number,
            "notification_url" => route('mercadopago.webhook'),
            "back_urls" => [
                "success" => config('app.frontend_url') . "/payment/success?order=" . $order->id,
                "failure" => config('app.frontend_url') . "/payment/failure?order=" . $order->id,
                "pending" => config('app.frontend_url') . "/payment/pending?order=" . $order->id,
            ],
            "auto_return" => "approved",
            "payment_methods" => [
                "excluded_payment_methods" => [],
                "excluded_payment_types" => [],
                "installments" => 12
            ]
        ]);

        return $preference;
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
} 