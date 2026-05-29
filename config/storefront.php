<?php

declare(strict_types=1);

/*
 * Configuração central da Vitrine do Cliente (Storefront).
 *
 * Define o mapa de perfis → tema padrão e feature flags padrão.
 * Cada tenant pode sobrescrever qualquer feature via tenant.data['features'].
 * O tema visual pode ser diferente do profile — é configurável independentemente.
 */
return [

    /*
     * Perfis disponíveis para tenants.
     *
     * - theme:    tema visual padrão para o perfil (pode ser sobrescrito em tenant.data['theme'])
     * - features: flags habilitadas por padrão; sobrescrevíveis individualmente por tenant
     */
    'profiles' => [

        // ─── Marketplaces ─────────────────────────────────────────────────────

        'esoterismo' => [
            'label' => 'Marketplace Esotérico',
            'theme' => 'esoterismo',
            'features' => [
                'agenda'                       => true,
                'blog'                         => true,
                'social_posts'                 => true,
                'reviews'                      => true,
                'marketplace'                  => true,
                'seller_events_on_marketplace' => true,
            ],
        ],

        'artesanato_marketplace' => [
            'label' => 'Marketplace de Artesanato',
            'theme' => 'artesanato',
            'features' => [
                'agenda'                       => true,
                'blog'                         => true,
                'social_posts'                 => true,
                'reviews'                      => true,
                'marketplace'                  => true,
                'seller_events_on_marketplace' => true,
            ],
        ],

        'cursos' => [
            'label' => 'Marketplace de Cursos Online',
            'theme' => 'cursos',
            'features' => [
                'agenda'                       => true,
                'blog'                         => true,
                'social_posts'                 => false,
                'reviews'                      => true,
                'marketplace'                  => true,
                'seller_events_on_marketplace' => true,
            ],
        ],

        'produtos_diversos' => [
            'label' => 'Marketplace de Produtos Diversos',
            'theme' => 'generico',
            'features' => [
                'agenda'                       => false,
                'blog'                         => false,
                'social_posts'                 => true,
                'reviews'                      => true,
                'marketplace'                  => true,
                'seller_events_on_marketplace' => false,
            ],
        ],

        // ─── Lojas individuais ─────────────────────────────────────────────────

        'loja_artesanato' => [
            'label' => 'Loja — Artesanato',
            'theme' => 'artesanato',
            'features' => [
                'agenda'                       => true,
                'blog'                         => true,
                'social_posts'                 => false,
                'reviews'                      => true,
                'marketplace'                  => false,
                'seller_events_on_marketplace' => false,
            ],
        ],

        'loja_roupas' => [
            'label' => 'Loja — Roupas',
            'theme' => 'roupas',
            'features' => [
                'agenda'                       => false,
                'blog'                         => true,
                'social_posts'                 => false,
                'reviews'                      => true,
                'marketplace'                  => false,
                'seller_events_on_marketplace' => false,
            ],
        ],

        'loja_armarinhos' => [
            'label' => 'Loja — Armarinhos',
            'theme' => 'armarinhos',
            'features' => [
                'agenda'                       => false,
                'blog'                         => true,
                'social_posts'                 => false,
                'reviews'                      => true,
                'marketplace'                  => false,
                'seller_events_on_marketplace' => false,
            ],
        ],

        'loja_eletronicos' => [
            'label' => 'Loja — Eletrônicos',
            'theme' => 'eletronicos',
            'features' => [
                'agenda'                       => false,
                'blog'                         => true,
                'social_posts'                 => false,
                'reviews'                      => true,
                'marketplace'                  => false,
                'seller_events_on_marketplace' => false,
            ],
        ],

        'generico' => [
            'label' => 'Loja Genérica',
            'theme' => 'generico',
            'features' => [
                'agenda'                       => false,
                'blog'                         => false,
                'social_posts'                 => false,
                'reviews'                      => true,
                'marketplace'                  => false,
                'seller_events_on_marketplace' => false,
            ],
        ],
    ],

    /*
     * Temas visuais disponíveis.
     * Cada tema corresponde a um diretório em resources/views/storefront/themes/{theme}/.
     * Apenas as views que diferem do fallback precisam existir no diretório do tema.
     */
    'themes' => [
        'generico'   => 'Genérico (padrão)',
        'esoterismo' => 'Esotérico',
        'artesanato' => 'Artesanato',
        'cursos'     => 'Cursos',
        'roupas'     => 'Moda e Roupas',
        'armarinhos' => 'Armarinhos',
        'eletronicos'=> 'Eletrônicos',
    ],

    /*
     * Labels para exibição das feature flags nos painéis.
     */
    'feature_labels' => [
        'agenda'                       => 'Agenda de Eventos e Cursos',
        'blog'                         => 'Blog Editorial',
        'social_posts'                 => 'Feed Social de Clientes',
        'reviews'                      => 'Avaliações de Produtos e Sellers',
        'marketplace'                  => 'Multi-Seller (Marketplace)',
        'seller_events_on_marketplace' => 'Eventos de Sellers na Vitrine do Marketplace',
    ],

];
