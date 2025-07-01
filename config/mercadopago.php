<?php

return [
    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
    'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
    'app_id' => env('MERCADOPAGO_APP_ID'),
    'client_secret' => env('MERCADOPAGO_CLIENT_SECRET'),
    'sandbox' => env('MERCADOPAGO_SANDBOX', true),
]; 