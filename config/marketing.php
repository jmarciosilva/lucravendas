<?php

declare(strict_types=1);

return [

    /*
     |--------------------------------------------------------------------------
     | Meta Graph API (Instagram + Facebook)
     |--------------------------------------------------------------------------
     | Credenciais do app criado em https://developers.facebook.com
     | Scopes necessários: pages_manage_posts, instagram_basic,
     |                     instagram_content_publish, pages_read_engagement
     */
    'meta' => [
        'app_id'      => env('META_APP_ID', ''),
        'app_secret'  => env('META_APP_SECRET', ''),
        'redirect_uri' => env('META_REDIRECT_URI', ''),
        'api_version' => env('META_API_VERSION', 'v19.0'),
        'sandbox'     => env('META_SANDBOX', true),
    ],

    /*
     |--------------------------------------------------------------------------
     | Post automático ao criar produto
     |--------------------------------------------------------------------------
     | Quando true, o listener ProductCreated agenda um post imediato em todas
     | as contas sociais ativas do tenant.
     */
    'auto_post_on_product_created' => (bool) env('MARKETING_AUTO_POST', false),

];
