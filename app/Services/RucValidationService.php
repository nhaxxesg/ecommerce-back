<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RucValidationService
{
    protected $baseUrl = 'https://api.apis.net.pe/v2/sunat/ruc';
    protected $token;

    public function __construct()
    {
        $this->token = config('services.apis_net.token');
    }

    public function validateRuc(string $ruc)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ])->get($this->baseUrl, [
                'numero' => $ruc
            ]);

            if (!$response->successful()) {
                Log::error('Error validando RUC', [
                    'ruc' => $ruc,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                throw new \Exception('Error al validar RUC: ' . $response->status());
            }

            $data = $response->json();

            // Validar que el RUC esté activo
            if ($data['estado'] !== 'ACTIVO') {
                throw new \Exception('El RUC no está activo');
            }

            return $data;

        } catch (\Exception $e) {
            Log::error('Error en validación de RUC', [
                'ruc' => $ruc,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 