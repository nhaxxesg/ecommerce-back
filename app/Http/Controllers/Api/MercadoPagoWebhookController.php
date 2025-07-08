<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            Log::info('MercadoPago Webhook: Received notification', [
                'headers' => $request->headers->all(),
                'payload' => $request->all()
            ]);

            // Solo verificar firma en modo producción
            if (config('services.mercadopago.mode') === 'production') {
                $this->verifySignature($request);
            }

            // La firma es válida o estamos en modo sandbox, procesar la notificación
            $payload = $request->json()->all();
            Log::info('MercadoPago Webhook: Processing notification', $payload);

            // Procesar según el tipo de notificación
            if (isset($payload['action'])) {
                switch ($payload['action']) {
                    case 'payment.created':
                    case 'payment.updated':
                        if (isset($payload['data']['id'])) {
                            $this->handlePaymentUpdate($payload['data']['id']);
                        }
                        break;
                    
                    // Puedes agregar más casos según necesites
                    default:
                        Log::info('MercadoPago Webhook: Unhandled action type', [
                            'action' => $payload['action']
                        ]);
                        break;
                }
            }

            return response()->json(['status' => 'success'], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('MercadoPago Webhook: Error processing webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function verifySignature(Request $request)
    {
        // 1. Obtener la firma de la cabecera 'x-signature'
        $signatureHeader = $request->header('x-signature');
        if (!$signatureHeader) {
            Log::error('MercadoPago Webhook: Signature header not found');
            abort(Response::HTTP_FORBIDDEN, 'Signature header not found.');
        }

        // 2. Separar el timestamp (ts) y el hash (v1)
        $parts = explode(',', $signatureHeader);
        $timestamp = null;
        $hash = null;

        foreach ($parts as $part) {
            [$key, $value] = explode('=', $part, 2);
            if ($key === 'ts') {
                $timestamp = $value;
            } elseif ($key === 'v1') {
                $hash = $value;
            }
        }

        if (!$timestamp || !$hash) {
            Log::error('MercadoPago Webhook: Invalid signature format', [
                'signature' => $signatureHeader
            ]);
            abort(Response::HTTP_FORBIDDEN, 'Invalid signature format.');
        }

        // 3. Obtener la clave secreta desde la configuración
        $secret = config('services.mercadopago.webhook_secret');

        // 4. Crear la plantilla para la firma
        $signedPayload = "{$timestamp}." . $request->getContent();

        // 5. Calcular la firma HMAC-SHA256
        $computedSignature = hash_hmac('sha256', $signedPayload, $secret);

        // 6. Comparar las firmas de forma segura
        if (!hash_equals($computedSignature, $hash)) {
            Log::error('MercadoPago Webhook: Invalid signature', [
                'computed' => $computedSignature,
                'received' => $hash
            ]);
            abort(Response::HTTP_FORBIDDEN, 'Invalid signature.');
        }
    }

    protected function handlePaymentUpdate($paymentId)
    {
        try {
            Log::info('MercadoPago Webhook: Handling payment update', ['payment_id' => $paymentId]);

            // En modo sandbox, simular una respuesta exitosa
            if (config('services.mercadopago.mode') === 'sandbox') {
                Log::info('MercadoPago Webhook: Sandbox mode - simulating payment update');
                return;
            }

            // Obtener los detalles del pago desde MercadoPago
            $mp = new \MercadoPago\SDK();
            $mp->setAccessToken(config('services.mercadopago.access_token'));
            $payment = \MercadoPago\Payment::find_by_id($paymentId);

            if (!$payment) {
                Log::error('MercadoPago Webhook: Payment not found', ['payment_id' => $paymentId]);
                return;
            }

            // Buscar el pago en nuestra base de datos
            $localPayment = Payment::where('payment_id', $paymentId)->first();
            if (!$localPayment) {
                Log::error('MercadoPago Webhook: Local payment not found', ['payment_id' => $paymentId]);
                return;
            }

            // Actualizar el estado del pago
            $localPayment->status = $payment->status;
            $localPayment->status_detail = $payment->status_detail;
            $localPayment->save();

            // Actualizar el estado del pedido según el estado del pago
            $order = Order::find($localPayment->order_id);
            if ($order) {
                switch ($payment->status) {
                    case 'approved':
                        $order->payment_status = 'paid';
                        $order->status = 'confirmed';
                        break;
                    case 'pending':
                        $order->payment_status = 'pending';
                        break;
                    case 'rejected':
                    case 'cancelled':
                        $order->payment_status = 'failed';
                        $order->status = 'cancelled';
                        break;
                }
                $order->save();

                Log::info('MercadoPago Webhook: Order updated successfully', [
                    'order_id' => $order->id,
                    'payment_status' => $order->payment_status,
                    'order_status' => $order->status
                ]);
            }

        } catch (\Exception $e) {
            Log::error('MercadoPago Webhook: Error handling payment update', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 