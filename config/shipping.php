<?php

declare(strict_types=1);

return [

    /*
     |--------------------------------------------------------------------------
     | Gateway de frete ativo
     |--------------------------------------------------------------------------
     | 'melhorenvio' → usa a API do Melhor Envio para cálculo em tempo real
     | 'internal'    → usa apenas o sistema de zonas e tarifas internas
     | 'both'        → retorna opções dos dois sistemas mescladas
     */
    'gateway' => env('SHIPPING_GATEWAY', 'both'),

    /*
     |--------------------------------------------------------------------------
     | Melhor Envio
     |--------------------------------------------------------------------------
     | Token gerado em: https://app.melhorenvio.com.br/tokens
     | Sandbox: https://sandbox.melhorenvio.com.br
     */
    'melhorenvio' => [
        'token'   => env('MELHORENVIO_TOKEN', ''),
        'sandbox' => env('MELHORENVIO_SANDBOX', true),

        // Dados do remetente padrão (usado na geração de etiquetas)
        'from' => [
            'name'      => env('MELHORENVIO_FROM_NAME', 'LucraVendas'),
            'email'     => env('MELHORENVIO_FROM_EMAIL', ''),
            'phone'     => env('MELHORENVIO_FROM_PHONE', ''),
            'document'  => env('MELHORENVIO_FROM_DOCUMENT', ''),   // CPF ou CNPJ
            'address'   => env('MELHORENVIO_FROM_ADDRESS', ''),
            'number'    => env('MELHORENVIO_FROM_NUMBER', ''),
            'city'      => env('MELHORENVIO_FROM_CITY', ''),
            'state'     => env('MELHORENVIO_FROM_STATE', ''),      // UF (2 chars)
            'zipcode'   => env('MELHORENVIO_FROM_ZIPCODE', ''),    // CEP de origem
        ],

        // Valor de seguro padrão em centavos (0 = desabilitado)
        'insurance_value_centavos' => env('MELHORENVIO_INSURANCE', 0),
    ],

    /*
     |--------------------------------------------------------------------------
     | Dimensões padrão do pacote (quando produto não tem dimensões cadastradas)
     |--------------------------------------------------------------------------
     */
    'default_package' => [
        'weight_grams' => (int) env('SHIPPING_DEFAULT_WEIGHT', 300),
        'length_cm'    => (int) env('SHIPPING_DEFAULT_LENGTH', 16),
        'width_cm'     => (int) env('SHIPPING_DEFAULT_WIDTH', 11),
        'height_cm'    => (int) env('SHIPPING_DEFAULT_HEIGHT', 5),
    ],

];
