<?php

declare(strict_types=1);

return [

    'mercadopago' => [
        'access_token'   => env('MERCADO_PAGO_ACCESS_TOKEN', ''),
        'public_key'     => env('MERCADO_PAGO_PUBLIC_KEY', ''),
        'webhook_secret' => env('MERCADO_PAGO_WEBHOOK_SECRET', ''),
        'sandbox'        => env('MERCADO_PAGO_SANDBOX', true),
    ],

];
